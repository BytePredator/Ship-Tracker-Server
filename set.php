<?php
    header('Content-Type: application/json');

    $json = file_get_contents('php://input');
    $data = json_decode($json);
    //$IdUser = intval($data->userId);
    $Table = isset($data->table) ? $data->table : "tracks";
    $Table = isset($_GET["table"]) ? $_GET["table"] : $Table;
    $IdUser = 1;
    $ret = array("error"=>1,"data"=>array());

    $link = mysqli_connect("localhost","ship-tracker","w7GZRNXYC98ZRUZj","ship_tracker");

    if (!$link) {
        echo "Error: Unable to connect to MySQL." . PHP_EOL;
        echo "Debugging errno: " . mysqli_connect_errno() . PHP_EOL;
        echo "Debugging error: " . mysqli_connect_error() . PHP_EOL;
	      exit;
    }
    switch ($Table) {
	    case 'tracks':
        //$ret["dbg"][]="ok";
        $ret["data"] = ["del"=>[], "new"=>[], "waypoints"=>[], "races"=>[]];
        foreach($data->delete as $row){
          $sql = "DELETE FROM tracks WHERE Id=".$row->id;
          $r = $link->query($sql);
          if(!$r){
            $ret["sql"] = $sql;
            $ret["dbg"] = $link->error;
          }
          $ret["data"]["del"][] = $row->id;
        }
        foreach($data->edit as $row){
          $sql = "UPDATE tracks SET Name='".$row->name."' WHERE Id=".$row->id;
          $r = $link->query($sql);
          if(!$r){
            $ret["sql"] = $sql;
            $ret["dbg"] = $link->error;
          }else{
            $ret["data"]["waypoints"] = saveWaypoints($link, $row->waypoints, $row->id);
            $ret["data"]["races"] = saveRaces($link, $row->races, $row->id);
          }
        }
        foreach($data->new as $row){
          $sql = "INSERT INTO tracks VALUES (NULL, $IdUser, '".$row->name."')";
          $r = $link->query($sql);
          if(!$r){
            $ret["sql"] = $sql;
            $ret["dbg"] = $link->error;
          }else{
            $ret["data"]["new"][] = [$row->id, $link->insert_id];
            foreach($row->waypoints as $w){
              $r = insertWaypoint($link, $w, $row->id);
              if($w->id != $r)
                $ret["data"]["waypoints"][] = [$w->id, $r];
            }
            foreach($row->races as $race){
              $r = insertRace($link, $race, $row->id);
              if($race->id != $r)
                $ret["data"]["races"][] = [$race->id, $r];
            }
          }
        }
        usort($ret["data"]["new"], 'reverseSort');
        usort($ret["data"]["waypoints"], 'reverseSort');
        usort($ret["data"]["races"], 'reverseSort');
        $ret["error"]=0;
        break;
    }
    file_put_contents("set.req",$json);
    echo json_encode($ret);
    return;

function saveWaypoints($link, $waypoints, $IdTrack){
  $add = [];
  $edit = [];
  $ret = [];
  foreach($waypoints as $waypoint){
    $r = getWaypoint($link, $waypoint->id);

    if($r->num_rows>0){
      $w = $r->fetch_assoc();
      if($w["Track"] == $IdTrack)
        $edit[] = $waypoint;
      else
        $add[] = $waypoint;
    }else
      $add[] = $waypoint;
  }
  foreach($add as $waypoint){
    $r = insertWaypoint($link, $waypoint, $IdTrack);
    $ret[] = [$waypoint->id, $r];
  }
  foreach($edit as $waypoint){
    updateWaypoint($link, $waypoint, $IdTrack);
  }
  return $ret;
}

function getWaypoint($link, $Id){
  return $link->query("SELECT * FROM waypoints WHERE Id=$Id");
}

function updateWaypoint($link, $waypoint, $IdTrack){
  $link->query("UPDATE waypoints SET Track=$IdTrack, Boa='".$waypoint->boa."', Number='".$waypoint->number."' WHERE Id = ".$waypoint->id);
  return $waypoint->id;
}

function insertWaypoint($link, $waypoint, $IdTrack){
  $r = $link->query("INSERT INTO waypoints VALUES ( NULL, $IdTrack, '".$waypoint->boa."', '".$waypoint->number."')");
  return $link->insert_id;
}




function saveRaces($link, $races, $IdTrack){
  $add = [];
  $edit = [];
  $ret = [];
  foreach($races as $race){
    $r = getRace($link, $race->id);

    if($r->num_rows>0){
      $row = $r->fetch_assoc();
      if($row["Track"] == $IdTrack)
        $edit[] = $race;
      else
        $add[] = $race;
    }else
      $add[] = $race;
  }
  foreach($add as $race){
    $r = insertRace($link, $race, $IdTrack);
    $ret[] = [$race->id, $r];
  }
  foreach($edit as $race){
    updateRace($link, $race, $IdTrack);
  }
  return $ret;
}

function getRace($link, $Id){
  return $link->query("SELECT * FROM races WHERE Id=$Id");
}

function updateRace($link, $race, $IdTrack){
  $link->query("UPDATE races SET Track=$IdTrack WHERE Id = ".$race->id);
  savePoints($link, $race->points, 1 ,$race->id);
  return $race->id;
}

function insertRace($link, $race, $IdTrack){
  $r = $link->query("INSERT INTO races VALUES ( NULL, $IdTrack)");
  savePoints($link, $race->points, 1 ,$link->insert_id);
  return $link->insert_id;
}




function savePoints($link, $points, $type, $IdRef){
  $add = [];
  $edit = [];
  $ret = [];
  foreach($points as $point){
    $r = getPoint($link, $point->id);
    if($r->num_rows>0){
      $row = $r->fetch_assoc();
      if($row["IdRef"] == $IdRef && $row["Type"] == $type)
        $edit[] = $point;
      else
        $add[] = $point;
    }else
      $add[] = $point;
  }
  foreach($add as $point){
    $r = insertPoint($link, $point, $type, $IdRef);
    $ret[] = [$point->id, $r];
  }
  foreach($edit as $point){
    updatePoint($link, $point, $type, $IdRef);
  }
  return $ret;
}

function getPoint($link, $Id){
  return $link->query("SELECT * FROM positions WHERE Id=$Id");
}

function updatePoint($link, $point, $type, $IdRef){
  $link->query("UPDATE waypoints SET IdRef=$IdRef, Type=$type, Latitude='".$point->latitude."', Longitude='".$point->longitude."', Time='".$point->time."' WHERE Id = ".$point->id);
  return $race->id;
}

function insertPoint($link, $point, $type, $IdRef){
  $r = $link->query("INSERT INTO waypoints VALUES ( NULL, $type, $IdRef, '".$point->latitude."', '".$point->longitude."', '".$point->time."')");
  return $link->insert_id;
}





function reverseSort($a, $b){
  return $b[1]-$a[1];
}
