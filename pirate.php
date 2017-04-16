<?php
/**
 * Auto-generated code below aims at helping you parse
 * the standard input according to the problem statement.
 **/
function dd($str){
    error_log(var_export($var, true));
}

// game loop
while (TRUE)
{
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
    }
    for ($i = 0; $i < $myShipCount; $i++)
    {

        echo ("MOVE 11 10\n"); // Any valid action, such as "WAIT" or "MOVE x y"
    }
}
?>