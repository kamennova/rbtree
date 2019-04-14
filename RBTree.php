<?php

abstract class Colours
{
    const red = 1;
    const black = 0;
}

abstract class Directions
{
    const right = 1;
    const left = 0;

    public static function get_opposite($dir_enum)
    {
        if ($dir_enum == Directions::right) return Directions::left;
        return Directions::right;
    }

    static function get_dir_str($dir_enum)
    {
        if ($dir_enum == Directions::right) return 'right';
        return 'left';
    }
}

class RBNode
{
    public $color; // 1 for red, 0 for black
    public $data;
    public $right;
    public $left;
    public $parent;

    function __construct($data = null)
    {
        $this->data = $data;
        $this->color = Colours::red;

        $this->right = null;
        $this->left = null;
        $this->parent = null;
    }

//    ----

    function get_parent()
    {
        return $this->parent; // NULL for root node
    }

    function get_grandparent()
    {
        $p = $this->get_parent();
        if ($p == null)
            return null; // No parent means no grandparent
        return $p->get_parent(); // NULL if parent is root
    }

    function get_sibling()
    {
        $p = $this->get_parent();
        if ($p == null)
            return null; // No parent means no sibling
        if ($this == $p->left)
            return $p->right;
        else
            return $p->left;
    }

    function get_uncle()
    {
        $p = $this->get_parent();
        $g = $this->get_grandparent();
        if ($g == null)
            return null; // No grandparent means no uncle

        return $p->get_sibling();
    }

    function rotate($dir_enum)
    {
        $dir = Directions::get_dir_str($dir_enum);
        $opposite = Directions::get_dir_str(Directions::get_opposite($dir_enum));

        $new_node = $this->$opposite;
        $parent = $this->get_parent();
        $this->$opposite = $new_node->$dir;
        $new_node->$dir = $this;
        $this->parent = $new_node;

        if ($parent != NULL) // initially n could be the root !!!PARENT CHECK!!!
        {
            if ($this == $parent->left)
                $parent->left = $new_node;
            else if ($this == $parent->right)
                $parent->right = $new_node;
        }

        $new_node->parent = $parent;
    }

    function insert_recurse($root)
    {
        if ($root == null) return;

        if ($this->data < $root->data) {
            if ($root->left != null) {
                $this->insert_recurse($root->left);
                return;
            } else {
                $root->left = $this;
            }
        } else {
            if ($root->right != null) {
                $this->insert_recurse($root->right);
                return;
            } else
                $root->right = $this;
        }

        $this->parent = $root;
    }
}


class RBTree
{
    public $root;
    public $count;

    function __construct()
    {
        $this->root = null;
    }

//    ---

    function is_leaf($node)
    {
        return $node == null;
    }

    function is_left_child($node)
    {
        return $node == $node->parent->left;
    }

    function is_right_child($node)
    {
        return $node == $node->parent->right;
    }

    function get_color($node)
    {
        return $node == null ? Colours::black : $node->color;
    }

//    ---

    function build_up($data_arr)
    {
        foreach ($data_arr as $data) {
            $this->insert($data);
        }
    }

//    ----

    function insert($data)
    {
        $node = new RBNode($data);
        $node->insert_recurse($this->root);
        $this->insert_repair_tree($node);

        // find the new root to return
        $root = $node;
        while ($root->get_parent() != NULL) {
            $root = $root->get_parent();
        }

        $this->root = $root;
    }

    function insert_repair_tree($node)
    {
        $parent = $node->get_parent();
        if ($parent == null) {
            $node->color = Colours::black;
        } else if ($parent->color == Colours::black) {
            return;
        } else if ($node->get_uncle() != NULL && $node->get_uncle()->color == Colours::red) {
            $this->insert_case3($node);
        } else {
            $this->insert_case4($node);
        }
    }

    function insert_case3($node)
    {
        $node->get_parent()->color = Colours::black;
        $node->get_uncle()->color = Colours::black;

        $grandparent = $node->get_grandparent();
        $grandparent->color = Colours::red;
        $this->insert_repair_tree($grandparent);
    }

    function insert_case4($node)
    {
        $parent = $node->get_parent();
        $grandparent = $node->get_grandparent();

        if ($node == $parent->right && $parent == $grandparent->left) {
            $parent->rotate(Directions::left);
            $node = $node->left;
        } else if ($node == $parent->left && $parent == $grandparent->right) {
            $parent->rotate(Directions::right);
            $node = $node->right;
        }

        if ($node == $parent->left)
            $grandparent->rotate(Directions::right);
        else
            $grandparent->rotate(Directions::left);
        $parent->color = Colours::black;
        $grandparent->color = Colours::red;
    }

    // --- delete ---

    function delete($data)
    {
        echo "Delete: " . $data . "\n";
        if ($node = $this->find($data)) {
            $this->delete_one_child($node);
            return true;
        }

        return false;
    }

    function replace_node($node, $child)
    {
        if (!$this->is_leaf($child))
            $child->parent = $node->parent;

        if ($node->parent == null) return;

        if ($this->is_left_child($node))
            $node->parent->left = $child;
        else
            $node->parent->right = $child;
    }

    function find_child_to_replace($node){

        if($this->is_leaf($node)) return null;

        if($node->left){
            $child = $node->left;
            while(!$this->is_leaf($child->right)){
                $child = $child->right;
            }
        } else {
            $child = $node->right;
            while(!$this->is_leaf($child->left)){
                $child = $child->left;
            }
        }

        return $child;
    }

    function delete_one_child($node)
    {
        // Precondition: node has at most one non-leaf child.
//        $child = $this->is_leaf($node->right) ? $node->left : $node->right;

        $child = $this->find_child_to_replace($node);
        $this->replace_node($node, $child);

        if (!$this->is_leaf($child)) {
            echo "Child: " . $child->data . " \n";

            if ($node->color == Colours::black) {
                if ($child->color == Colours::red)
                    $child->color = Colours::black;
                else
                    $this->delete_case1($child);
            }
        }

        $node = null; // todo
    }

    function delete_case1($node)
    {
        if ($node->get_parent() != NULL)
            $this->delete_case2($node);
    }

    function delete_case2($node)
    {
        $sib = $node->get_sibling();

        if ($this->get_color($sib) == Colours::red) {
            $node->parent->color = Colours::red;
            $sib->color = Colours::black;
            if ($node == $node->parent->left)
                $node->parent->rotate(Directions::left);
            else
                $node->parent->rotate(Directions::right);
        }

        $this->delete_case3($node);
    }

    function delete_case3($node)
    {
        $sib = $node->get_sibling();

        if ($this->get_color($node->parent) == Colours::black && $this->get_color($sib) == Colours::black &&
            $this->get_color($sib->left) == Colours::black && $this->get_color($sib->right) == Colours::black) {
            $sib->color = Colours::red;// todo
            $this->delete_case1($node->parent);
        } else
            $this->delete_case4($node);
    }

    function delete_case4($node)
    {
        $sib = $node->get_sibling();

        if ($node->parent->color == Colours::red &&
            $sib->color == Colours::black &&
            $sib->left->color == Colours::black &&
            $sib->right->color == Colours::black) {
            $sib->color = Colours::red;
            $node->parent->color = Colours::black;
        } else
            $this->delete_case5($node);
    }

    function delete_case5($node)
    {
        $sib = $node->get_sibling();

        if ($this->get_color($sib) == Colours::black) {
            if ($this->is_left_child($node) && $this->get_color($sib->right) == Colours::black &&
                $this->get_color($sib->left) == Colours::red) { /* this last test is trivial too due to cases 2-4. */
                $sib->color = Colours::red;
                $sib->left->color = Colours::black;
                $sib->rotate(Directions::right);
            } else if ($this->is_right_child($node) && $this->get_color($sib->left) == Colours::black &&
                $this->get_color($sib->right) == Colours::red) {/* this last test is trivial too due to cases 2-4. */
                $sib->color = Colours::red;
                $sib->right->color = Colours::black;
                $sib->rotate(Directions::left);
            }
        }

        $this->delete_case6($node);
    }

    function delete_case6($node)
    {
        $sib = $node->get_sibling();

        $sib->color = $node->parent->color;
        $node->parent->color = Colours::black;

        if ($this->is_left_child($node)) {
            $sib->right->color = Colours::black;
            $node->parent->rotate(Directions::left);
        } else {
            $sib->left->color = Colours::black;
            $node->parent->rotate(Directions::right);
        }
    }

//    ---

    function infix()
    {
        $this->infix_step($this->root);
        echo "\n";
    }

    function infix_step($node)
    {
        if ($node == null) return;
        $this->infix_step($node->right);
        echo $node->data . ' ';
        $this->infix_step($node->left);
    }

//    ---

    function find($data)
    {
        return $this->find_step($this->root, $data);
    }

    function find_step($curr_node, $data)
    {
        if ($curr_node == null) return false;

        if ($data > $curr_node->data) {
            return $this->find_step($curr_node->right, $data);
        } else if ($data < $curr_node->data) {
            return $this->find_step($curr_node->left, $data);
        } else return $curr_node; // data = current node data => found
    }
}