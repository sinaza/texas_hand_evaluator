<?php

class TexasEvaluator {

    private $hand = array();
    private $handInt = array();
    private $handSuits = array();
    private $handIntFreq = array();
    private $handIntDiffFreq = array();
    private $handSuitsFreq = array();
    private $straightHands = array();
    private $status = array('Straight Flush' => False,
                            'Four'           => False,
                            'Full House'     => False,
                            'Flush'          => False,
                            'Straight'       => False,
                            'Three'          => False,
                            'Two Pair'       => False,
                            'Pair'           => False,
                            'High Card'      => False);

    /**
     * Class constructor. Determines appearance of numbers, suites and
     * again frequency of "numbers' appearance" itself.
     *
     * @param array $hand Represent the hand
     */
    public function __construct($hand)
    {
        $this->hand = $hand;

        $this->__explodeHand();

        $this->handIntFreq = $this->__detectFrequency($this->handInt);
        $this->handSuitsFreq = $this->__detectFrequency($this->handSuits);
        $this->handIntDiffFreq = $this->__detectFrequency($this->handIntFreq);

    }

    /**
     * Evaluates the hand and echos the json_decoded result.
     *
     * @return void
     */
    public function evaluate()
    {
        foreach(array_keys($this->status) as $key)
        {
            $func = '__is' . str_replace(' ', '', $key);
            $this->status[$key] = call_user_func_array(array($this, $func), array());

            if($this->status[$key])
                break;
        }

        echo json_encode($this->status);
    }

    /**
     * Fill suites in handSuits property and numbers in handInt property.
     *
     * @return void
     */
    protected function __explodeHand()
    {
        foreach($this->hand as $card)
        {
            $this->handInt[] = key($card);
            $this->handSuits[] = array_shift($card);
        }
    }

    /**
     * Counts the values which can represent numbers or suits.
     *
     * @param  array $arr
     * @return array
     */
    protected function __detectFrequency($arr)
    {
        return array_count_values($arr);
    }

    /**
     * If the hand is four or not.
     *
     * @return boolean
     */
    protected function __isFour()
    {
        return array_key_exists(4, $this->handIntDiffFreq);
    }

    /**
     * If the hand is three-of-a-king or not.
     *
     * @return boolean
     */
    protected function __isThree()
    {
        return array_key_exists(3, $this->handIntDiffFreq);
    }

    /**
     * If the hand is pair or not.
     *
     * @return boolean
     */
    protected function __isPair()
    {
        return array_key_exists(2, $this->handIntDiffFreq);
    }

    /**
     * If the hand is two-pair or not.
     *
     * @return boolean
     */
    protected function __isTwoPair()
    {
        return ($this->__isPair() && $this->handIntDiffFreq[2] >= 2) ? True : False;
    }

    /**
     * If the hand is flush or not.
     *
     * @return boolean
     */
    protected function __isFlush()
    {
        foreach($this->handSuitsFreq as $val)
            if($val >= 5)
                return True;

        return False;
    }

    /**
     * If the hand is straight or not.
     *
     * @return boolean
     */
    protected function __isStraight()
    {
        $this->__extractStraightHands();

        return (!empty($this->straightHands)) ? True : False;
    }

    /**
     * If the hand is full-house or not.
     *
     * @return boolean
     */
    protected function __isFullHouse()
    {
        return ($this->__isThree() && ($this->__isPair() || $this->handIntDiffFreq[3] == 2)) ? True : False;
    }

    /**
     * If the hand is straight-flush or not.
     *
     * @return boolean
     */
    protected function __isStraightFlush()
    {
        $sfHand = $this->__extractStraightFlushHand();

        if($sfHand && $this->__isStraight())
        {
            foreach($this->straightHands as $straightHand)
            {
                if(strpos(implode($sfHand, ''), implode($straightHand, '')) !== False)
                    return True;
            }
        }

        return False;
    }

    /**
     * If the hand is high-card or not.
     *
     * @return boolean
     */
    protected function __isHighCard()
    {
        return (in_array(True, array_unique($this->status))) ? False : True;
    }

    /**
     * Calculates straight hands and sets it to straightHands property.
     *
     * @return void
     */
    protected function __extractStraightHands()
    {
        $hand = array_unique($this->handInt);

        if(in_array(1, $hand))
            $hand[] = 14;

        sort($hand);

        foreach($hand as $key => $card)
        {
            $lastIndex = $key + 4;
            $lastCard  = isset($hand[$lastIndex]) ? $hand[$lastIndex] : False;

            if(isset($lastCard))
            {
                if(array_slice($hand, $key, 5) == range($card, $lastCard))
                    $this->straightHands[] = range($card, $lastCard);
            }
        }
    }

    /**
     * @return array Returns straight-flush hand.
     */
    protected function __extractStraightFlushHand()
    {
        if(!$this->__isFlush())
            return False;

        $sfHand = array();
        $handSuits = $this->handSuitsFreq;
        arsort($handSuits);
        $suit = array_shift(array_flip($handSuits));

        foreach($this->hand as $card)
            if(in_array($suit, $card))
                $sfHand[] = key($card);

        if(in_array(1, $sfHand))
            $sfHand[] = 14;

        sort($sfHand);

        return $sfHand;
    }
}
