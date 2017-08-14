<?php

class CombinationFragment extends \InfiniteIterator
{
    private $firstItem;

    public function __construct($charset)
    {
        if (is_scalar($charset)) {
            $charset = str_split($charset, 1);
        }

        $this->firstItem = $charset[0];

        parent::__construct(new ArrayIterator($charset));
        $this->rewind();
    }

    public function isBeginning()
    {
        return $this->current() == $this->firstItem;
    }
}