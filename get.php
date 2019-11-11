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
            if(!($result = $link->query("SELECT tracks.Id, Name, IFNULL((
                    SELECT MAX(Time)-MIN(Time)
                    FROM positions
                    WHERE positions.Type=1 AND positions.IdRef = races.Id
                    GROUP BY IdRef HAVING COUNT(positions.Id) = (select COUNT(Id) FROM waypoints WHERE Track=tracks.Id)
                ), 0) AS Time
                FROM races
                RIGHT JOIN tracks ON tracks.Id=races.Track
                WHERE User='$IdUser'
                GROUP BY tracks.Id, Name, Time HAVING Time = MIN(Time)
                ORDER BY Name ASC"))){
                printf("Error: %s\n", $link->error);
            }else{
                if($result->num_rows)
                    $ret["data"] = $result->fetch_all(MYSQLI_NUM);
        	    foreach($ret["data"] as $k=>$v){
            		$result2 = $link->query("SELECT w.Id, boas.*, w.Number FROM boas RIGHT JOIN waypoints AS w ON w.Boa=boas.Id WHERE Track =".$v[0]." ORDER BY w.Number ASC");
                    $ret["data"][$k][3] = array();
                    if(!$result2)
            		    printf("ERROR2 %s\n", $link->error);
            		elseif($result2->num_rows>0){
                        $tmp = $result2->fetch_all(MYSQLI_NUM);
                        foreach ($tmp as $waypoint) {
                		    $ret["data"][$k][3][] = array($waypoint[0], array($waypoint[1],$waypoint[2],$waypoint[3]), $waypoint[4]);
                        }
                    }
                    $result3 = $link->query("SELECT races.Id, COALESCE(MAX(Time) - MIN(Time), 0) AS time
                                            FROM positions
                                            RIGHT JOIN races ON races.Id = positions.IdRef
                                            WHERE Track =".$v[0]."
                                            GROUP BY races.Id");
                    $ret["data"][$k][4] = array();
                    if(!$result3)
            		    printf("ERROR3 %s\n", $link->error);
            		else
                        while ($row = $result3->fetch_row())
            		          $ret["data"][$k][4][] = array($row[0], $row[1]);
        	    }
        	    $ret["error"] = 0;
            }
        break;
        case 'races':
            if(!($result = $link->query("SELECT tracks.Id, races.Id, COALESCE(SUM(Distance),0) AS Distance, COALESCE((MAX(diff.Time)-MIN(diff.Time)),0) AS Time
                FROM (
                    SELECT point1.IdRef, point1.Type, haversine(point1.Latitude, point1.Longitude, point2.Latitude, point2.Longitude) AS Distance, point1.Time
                    FROM (
                        SELECT *, (
                            SELECT Id
                            FROM positions AS p
                            WHERE p1.Time<p.Time AND p1.Type=p.Type AND p1.IdRef=p.IdRef
                            ORDER BY Time ASC
                            LIMIT 1
                        ) AS Id2
                        FROM positions AS p1
                    ) AS point1
                    LEFT JOIN positions AS point2 ON point2.Id = COALESCE(point1.Id2, point1.Id)
                ) AS diff
                RIGHT JOIN races ON races.Id=diff.IdRef AND diff.Type = 1
                LEFT JOIN tracks ON races.Track=tracks.Id
                WHERE User=1
                GROUP BY races.Id, Name
                ORDER BY tracks.Id, Time ASC"))){
                    printf("Error: %s\n", $link->error);
            }else{
                if($result->num_rows)
                    $ret["data"] = $result->fetch_all(MYSQLI_NUM);
                $ret["error"] = 0;
            }
            break;
        case 'race':
            $id = intval($data->raceId);
            if(!($result = $link->query("SELECT Id, Latitude, Longitude, Time FROM positions WHERE Type = 1 AND IdRef = $id"))){
                printf("Error: %s\n", $link->error);
            }else{
                if($result->num_rows){
                    $ret["data"] = $result->fetch_all(MYSQLI_NUM);
		    foreach($ret["data"] as $k=>$v)
		    	$ret["data"][$k][3] = strtotime($ret["data"][$k][3]);
		}
                $ret["error"] = 0;
            }
            break;
        case 'boas':
            if(!($result = $link->query("SELECT * FROM boas"))){
                printf("Error: %s\n", $link->error);
            }else{
                if($result->num_rows)
                    $ret["data"] = $result->fetch_all(MYSQLI_NUM);
                $ret["error"] = 0;
            }
            break;
        case 'traces':
            if(!($result = $link->query("SELECT traces.Id, Name,  SUM(Distance) AS Distance, (MAX(diff.Time)-MIN(diff.Time)) AS Time
                FROM (
                    SELECT adj.IdRef, adj.Type, haversine(point1.Latitude, point1.Longitude, point2.Latitude, point2.Longitude) AS Distance, adj.Time
                    FROM
                        (SELECT p1.Id AS Id1, p1.Type, p1.IdRef, p1.Time,
                            (SELECT Id FROM positions AS p WHERE p1.Time<p.Time AND p1.Type=p.Type AND p1.IdRef=p.IdRef
                            ORDER BY Time ASC LIMIT 1) AS Id2
                        FROM positions AS p1) AS adj

                    LEFT JOIN positions AS point1 ON point1.Id=adj.Id1
                    LEFT JOIN positions AS point2 ON point2.Id=COALESCE(adj.Id2, adj.Id2, adj.Id1)
                ) AS diff
                RIGHT JOIN traces ON traces.Id=diff.IdRef AND diff.Type = 0
                WHERE User='$IdUser'
                GROUP BY traces.Id, Name
                ORDER BY Name ASC"))){
                printf("Error: %s\n", $link->error);
            }else{
                if($result->num_rows)
                    $ret["data"] = $result->fetch_all(MYSQLI_NUM);
                $ret["error"] = 0;
            }
            break;
        default:
            $ret["error"] = 1;
            break;
    }

    mysqli_close($link);

    echo json_encode($ret);
