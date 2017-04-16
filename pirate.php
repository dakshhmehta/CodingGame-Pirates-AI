<?php

function dd($str){
    error_log(var_export($str, true));
}

function by_distance_ASC($a, $b){
    return $a['distance'] > $b['distance'];
}

$board = [];

$game = []; // Master game state data;

function distance($entity1, $entity2){
    $d = (
        abs($entity1['xp'] - $entity2['xp']) 
    +   abs($entity1['yp'] - $entity2['yp']) 
    +   abs($entity1['zp'] - $entity2['zp'])
    ) / 2;

    return $d;
}

function optimize_board()
{
    global $board, $game;

    if(! isset($game['barrel'])) return false; // We do not have any barrels

    foreach ($game['barrel'] as &$barrel) {
        foreach($game['ship'][1] as &$ship){
            foreach($game['ship'][0] as &$opponent){
                $d = distance($opponent, $barrel);

                $barrel['opponent_distance'][] = [
                    'id' => $opponent['id'],
                    'distance' => $d
                ];

                $d2 = distance($ship, $opponent);

                $ship['opponent_distance'][] = [
                    'id' => $opponent['id'],
                    'distance' => $d2
                ];
            }

            $d = distance($ship, $barrel);

            $ship['barrel_distance'][] = [
                'id' => $barrel['id'],
                'distance' => $d
            ];
        }
    }
}

// game loop
while (TRUE)
{
    $game = [];
    for($i = 0; $i < 23; $i++){
        for($j = 0; $j<21; $j++){
            $board[$i][$j] = 0;
        }
    }

    fscanf(STDIN, "%d",
        $myShipCount // the number of remaining ships
    );
    fscanf(STDIN, "%d",
        $entityCount // the number of entities (e.g. ships, mines or cannonballs)
    );
    for ($i = 0; $i < $entityCount; $i++)
    {
        fscanf(STDIN, "%d %s %d %d %d %d %d %d",
            $entityId,
            $entityType,
            $x,
            $y,
            $arg1,
            $arg2,
            $arg3,
            $arg4
        );

        $xp = $x - ($y - ($y & 1)) / 2;
        $zp = $y;
        $yp = -($xp + $zp);

        if($entityType == 'SHIP'){
            $game['ship'][$arg4][] = [
                'id' => $entityId,
                'rotation' => $arg1,
                'speed' => $arg2,
                'stock' => $arg3,
                'xp' => $xp,
                'yp' => $yp,
                'zp' => $zp,
                'x' => $x,
                'y' => $y,
            ];

            $board[$x][$y] += $arg4;
        }
        elseif ($entityType == 'BARREL') {
            $game['barrel'][$entityId] = [
                'id' => $entityId,
                'stock' => $arg1,
                'xp' => $xp,
                'yp' => $yp,
                'zp' => $zp,
                'x' => $x,
                'y' => $y,
            ];

            $board[$x][$y] += 100;
        }
        elseif ($entityType == 'CANNONBALL') {
            $game['canon'][] = [
                'id' => $entityId,
                'x' => $x,
                'y' => $y,
                'by' => $arg1,
                'incoming_in' => $arg2,
            ];

            $board[$x][$y] -= 50 + $arg2;
        }
    }

    optimize_board();

    foreach($game['ship'][1] as $shipID => $ship)
    {
        if(! isset($game['barrel'])){
            $nearestOpponentShip = $game['ship'][0][0];
            $x = $nearestOpponentShip['x'];
            $y = $nearestOpponentShip['y'];

            echo ("FIRE $x $y\n");
            continue;
        }

        usort($ship['barrel_distance'], 'by_distance_ASC');
        //dd($ship['barrel_distance']);
        $nearestBarrel = $ship['barrel_distance'][0];
        $distance = $nearestBarrel['distance'];
        $barrel = $game['barrel'][$nearestBarrel['id']];

        // If enemy is reaching faster than us, lets canon it!
        usort($barrel['opponent_distance'], 'by_distance_ASC');
        dd($barrel['opponent_distance']);

        $distance = $barrel['opponent_distance'][0]['distance'] - $distance;
        dd('Distance is '.$distance);

        dd('We will reach faster');
        $x = $barrel['x'];
        $y = $barrel['y'];
        echo ("MOVE $x $y\n"); // Any valid action, such as "WAIT" or "MOVE x y"
    }
}
?>