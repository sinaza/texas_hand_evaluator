<?php

include "texasevaluator.php";

/**
 * Example of a $hand:
 *
 * $hand = array(array('2'=>'c'), array('2'=>'h'), array('3'=>'s'), array('4'=>'h'), array('5'=>'c'), array('7'=>'h'), array('6'=>'c'));
 *
 * Each array represents a number and its suit.
 *
 * Suits:
 *  - C is Club
 *  - D is Diamond
 *  - H is Heart
 *  - S is Spade
 */
$hand = json_decode($_POST['hand'], true);

$texasEvaluator = new TexasEvaluator($hand);
$texasEvaluator->evaluate();
?>
