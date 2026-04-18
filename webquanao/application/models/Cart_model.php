<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Cart_model extends MY_Model
{
    var $table = 'cart';
    var $key = 'id';

    /**
     * Get cart items for a specific user
     * 
     * @param int $user_id The user ID
     * @return array Array of cart items
     */
    public function get_items_by_user($user_id)
    {
        $this->db->where('user_id', $user_id);
        $query = $this->db->get($this->table);
        return $query->result();
    }

    /**
     * Check if a product exists in user's cart
     * 
     * @param int $user_id The user ID
     * @param int $product_id The product ID
     * @param string $options Serialized options (if any)
     * @return mixed Returns the cart item if found, FALSE otherwise
     */
    public function item_exists($user_id, $product_id, $options = '')
    {
        $this->db->where('user_id', $user_id);
        $this->db->where('product_id', $product_id);

        if (!empty($options)) {
            $this->db->where('options', $options);
        }

        $query = $this->db->get($this->table);

        if ($query->num_rows() > 0) {
            return $query->row();
        }

        return FALSE;
    }

    /**
     * Add or update cart item for a user
     * 
     * @param int $user_id The user ID
     * @param array $item Cart item data
     * @return boolean TRUE on success, FALSE on failure
     */
    public function save_item($user_id, $item)
    {
        // Check if item exists
        $options = isset($item['options']) ? serialize($item['options']) : '';
        $existing = $this->item_exists($user_id, $item['id'], $options);

        $data = array(
            'user_id' => $user_id,
            'product_id' => $item['id'],
            'qty' => $item['qty'],
            'price' => $item['price'],
            'name' => $item['name'],
            'options' => $options,
            'rowid' => $item['rowid']
        );

        if (isset($item['image_link'])) {
            $data['image_link'] = $item['image_link'];
        }

        if ($existing) {
            // Update quantity if item exists
            $this->db->where('id', $existing->id);
            return $this->db->update($this->table, $data);
        } else {
            // Insert new item
            return $this->db->insert($this->table, $data);
        }
    }

    /**
     * Remove a specific cart item
     * 
     * @param int $user_id The user ID
     * @param string $rowid The row ID of the cart item
     * @return boolean TRUE on success, FALSE on failure
     */
    public function remove_item($user_id, $rowid)
    {
        $this->db->where('user_id', $user_id);
        $this->db->where('rowid', $rowid);
        return $this->db->delete($this->table);
    }

    /**
     * Clear all cart items for a specific user
     * 
     * @param int $user_id The user ID
     * @return boolean TRUE on success, FALSE on failure
     */
    public function clear_cart($user_id)
    {
        $this->db->where('user_id', $user_id);
        return $this->db->delete($this->table);
    }

    /**
     * Update cart item quantity
     * 
     * @param int $user_id The user ID
     * @param string $rowid The row ID of the cart item
     * @param int $qty The new quantity
     * @return boolean TRUE on success, FALSE on failure
     */
    public function update_qty($user_id, $rowid, $qty)
    {
        $this->db->where('user_id', $user_id);
        $this->db->where('rowid', $rowid);
        return $this->db->update($this->table, array('qty' => $qty));
    }

    public function get_cart_with_catalog_id($where = array())
    {
        $this->db->select('cart.*, product.catalog_id, catalog.parent_id');
        $this->db->from('cart');
        $this->db->join('product', 'product.id = cart.product_id', 'left');
        $this->db->join('catalog', 'catalog.id = product.catalog_id', 'left'); // JOIN thêm bảng catalog

        if (!empty($where)) {
            $this->db->where($where);
        }

        $query = $this->db->get();
        return $query->result();
    }
}
