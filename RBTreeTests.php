<?php

class RBTreeTests
{
    public function build_up_self_balanced()
    {
//        assert_options(ASSERT_CALLBACK, 'assert_handler');
        $tree = new RBTree();
        $tree->build_up([0, 5, 7]);

        assert_options(ASSERT_WARNING, 0);

        echo $tree->root->data . "\n";

        assert($tree->root->data == 4, RuntimeException::class);
    }
}


function assert_handler($file, $line, $code)
{
    echo "Assertion Failed:
        File '$file' " . "\n" . "
        Line '$line' " . "\n" . "
        Code '$code'" . "\n";
}