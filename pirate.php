<?php

function dd($str){
    error_log(var_export($str, true));
}

function by_distance_ASC($a, $b){
    return $a['distance'] > $b['distance'];
}

$board = [];

$game = []; // Master game state data;

function optimize_board()
{
    global $board, $game;

    foreach($game['ship'][1] as &$ship){
        foreach ($game['barrel'] as &$barrel) {
            $d = (
                abs($ship['xp'] - $barrel['xp']) 
            +   abs($ship['yp'] - $barrel['yp']) 
            +   abs($ship['zp'] - $barrel['zp'])
            ) / 2;

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

            $board[$x][$y] += 10;
        }
    }

    optimize_board();

    foreach($game['ship'][1] as $shipID => $ship)
    {
        usort($ship['barrel_distance'], 'by_distance_ASC');
        $nearestBarrel = $ship['barrel_distance'][0];
        $x = $game['barrel'][$nearestBarrel['id']]['x'];
        $y = $game['barrel'][$nearestBarrel['id']]['y'];
        dd([$nearestBarrel, $ship]);
        echo ("MOVE $x $y\n"); // Any valid action, such as "WAIT" or "MOVE x y"
    }
}
?>