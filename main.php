<?php

require_once 'RBTree.php';
require_once 'RBTreeTests.php';

$tree = new RBTree();
$tree->build_up([0, 5, 9, 12, 4, 6]);
$tree->infix();
$tree->delete(12);
$tree->infix();
echo $tree->root->data . ' ';

$tree->delete(5);
$tree->infix();
echo $tree->root->data . ' ';

//$test_tree = new RBTreeTests();
//$test_tree->build_up_self_balanced();