<?php
@session_start();

include_once 'database/Database.php';
include_once 'database/Products.php';
include_once 'database/Invoice.php';
include_once 'database/Balance.php';

class Order {
    
    /**
     * Create new order header
     */
    public static function insertHeader($db, $customer_id, $status = 'Open') {
    	$q = "begin insert_order_header(:ccust_id, :cstatus); end;";
        $stid = $db->parseQuery($q);
        // Get the cust id only
        oci_bind_by_name($stid, ':ccust_id', $customer_id);
        oci_bind_by_name($stid, ':cstatus', $status);
        $r = oci_execute($stid);  // executes and commits
        return $r;
    }

	public static function insertRow($index, $order_id, $p_id, $quantity, $db) {
    	$q = "begin insert_order_row(:corder_id, :crow_num, :cp_id, :cquantity); end;";
    	$stid = $db->parseQuery($q);
    	oci_bind_by_name($stid, ':corder_id', $order_id);
    	oci_bind_by_name($stid, ':crow_num', $index);
    	oci_bind_by_name($stid, ':cp_id', $p_id);
    	oci_bind_by_name($stid, ':cquantity', $quantity);
    	$r = oci_execute($stid);  // executes and commits
    	return $r;
    }
    
    /**
     * Get the last record added 
     */
    public static function getLastAdded($db) {
    	$q = "select get_last_order as last from dual";
    	$result = $db->createQuery($q);
    	return $result;
    }
    
	public static function getOrderHeader($order_id, $db) {
		$q = "SELECT * FROM table(GET_ORDERS_HEADER('{$order_id}', 1, 99999))";
		$result = $db->createQuery($q);
		if (count($result) > 0) {
			return $result;
		} else {
			return null;
		}
	}

	/**
	 * Find the order rows by id
	 * @param int $order_id
	 * @return array of order rows if found or FALSE otherwise
	 */
	public static function getOrderRows($order_id, $db) {
		$q = "select * from table(GET_ORDER_ROWS({$order_id},1,999))";
		$result = $db->createQuery($q);
		if (count($result) > 0) {
			return $result;
		} else {
			return FALSE;
		}
	}


	public static function getOrderHeaders($db) {
		$q = "SELECT * FROM table(GET_ORDERS_HEADERS(1, 99999))";
		$result = $db->createQuery($q);
		if (count($result) > 0) {
			return $result;
		} else {
			return array();
		}
	}

	public static function getOrderHeadersFilterDate($db, $fromDate, $toDate) {
		$q = "SELECT * FROM table(GET_ORDERS_HEADERS_FILTER_DATE(1, 99999, to_date('{$fromDate}', 'yyyy-mm-dd'), to_date('{$toDate}', 'yyyy-mm-dd')))";
		$result = $db->createQuery($q);
		if (count($result) > 0) {
			return $result;
		} else {
			return array();
		}
	}


	/**
	 * Cancel the order
	 * Make status -> 'Close'
	 */
	public static function cancelOrder($order_id, $db) {
		$q = "begin update_status(:corder_id, 'Cancel'); end;";
		$stid = $db->parseQuery($q);
		oci_bind_by_name($stid, ':corder_id', $order_id);
		oci_execute($stid);  // executes and commits
	}

	public static function deleteOrderRows($order_id) {
		$db = new Database();
		$q = "begin delete_order_row(:corder_id); end;";
		$stid = $db->parseQuery($q);
		oci_bind_by_name($stid, ':corder_id', $order_id);
		oci_execute($stid);  // executes and commits
	}

//    /**
//     * Delete an order by its id
//     * @param int $order_id
//     */
//    public static function deleteOrder($order_id) {
//    	$db = new Database();
//    	// Delete all rows
//    	$q = "delete from orders_rows where (order_id = :corder_id)";
//    	$stid = $db->parseQuery($q);
//    	oci_bind_by_name($stid, ':corder_id', $order_id);
//    	oci_execute($stid); // delete rows
//
//    	// Delete the header
//    	$q = "delete from orders_header where (order_id = :corder_id)";
//    	$stid = $db->parseQuery($q);
//    	oci_bind_by_name($stid, ':corder_id', $order_id);
//    	oci_execute($stid); // delete header
//    }


//    /**
//     * Get the order details
//     * @param int $order_id
//     * @param int $cust_id
//     * @param Date $start_date
//     * @param Date $end_date
//     * @param String $first_name
//     * @param String $last_name
//     * @return array of orders
//     */
//    public static function getOrdersDetails($order_id, $cust_id, $start_date, $end_date, $first_name, $last_name) {
//    	$db = new Database();
//
//    	$customers = Customer::getCustomersDetails($cust_id, $first_name, $last_name, $db);
//
//    	if(!$customers) {
//    		$cust_ids = "NULL";
//    	} else {
//    		$cust_ids = "";
//    		foreach ($customers as $index=>$customer) {
//    			$cust_ids .= ($customer['CUST_ID'].',');
//    		}
//    		$cust_ids[strlen($cust_ids)-1] = "";
//    	}
//
//    	// Get the right date format to insert
//    	if(!empty($start_date)) {
//    		$start = date("d/m/Y", strtotime($start_date));
//    	} else {
//    		$start = NULL;
//    	}
//    	if(!empty($end_date)) {
//    		$end = date("d/m/Y", strtotime($end_date));
//    	} else {
//    		$end = NULL;
//    	}
//
//    	$q = "select i.order_id, to_char(i.order_date, 'DD/MM/YYYY') as order_date, i.cust_id, i.status, c.first_name, c.last_name from orders_header i, customers c where i.cust_id=c.cust_id and i.order_id='{$order_id}'
//	    	UNION
//	    	select i.order_id, to_char(i.order_date, 'DD/MM/YYYY') as order_date, i.cust_id, i.status, c.first_name, c.last_name from orders_header i, customers c where i.cust_id=c.cust_id and (i.order_date >= to_date('{$start}', 'dd/mm/yyyy') or i.order_date <= to_date('{$end}', 'dd/mm/yyyy'))
//	    	UNION
//	    	select i.order_id, to_char(i.order_date, 'DD/MM/YYYY') as order_date, i.cust_id, i.status, c.first_name, c.last_name from orders_header i, customers c where  i.cust_id=c.cust_id and i.cust_id IN ({$cust_ids})";
//    	$results = $db->createQuery($q);
//    	return $results;
//    }

//    public static function getOrdersCount($db) {
//    	$q = "select count(*) as count from orders_header where status LIKE 'Open'";
//    	$result = $db->createQuery($q);
//    	return $result[0]['COUNT'];
//    }

//    /**
//     * Update an order
//     */
//    public static function editOrder() {
//    	$db = new Database();
//    	// Update Header
//    	$q = "begin update_status(:corder_id, :cstatus); end;";
//    	$stid = $db->parseQuery($q);
//    	oci_bind_by_name($stid, ':corder_id', $_POST['order_id']);
//    	oci_bind_by_name($stid, ':cstatus', $_POST['status']);
//    	oci_execute($stid);  // executes and commits
//
//    	// Update Rows
//    	$i = 0;
//    	while(isset($_POST['quantity_'.$i])) {
//    		$q = "select max(row_num) as last from orders_rows r, products p where (r.p_id = p.p_id and r.order_id = '{$_POST['order_id']}')";
//    		$last_row = $db->createQuery($q)[0]['LAST'];
//
//    		$q = "select * from orders_rows r, products p where (r.p_id = p.p_id and r.order_id = '{$_POST['order_id']}' and r.p_id='{$_POST['p_id_'.$i]}')";
//    		$product_row = $db->createQuery($q);
//
//    		if(count($product_row) > 0) { // Exist product
//    			$row_num = $product_row[0]['ROW_NUM'];
//    			if($_POST['quantity_'.$i] == 0) { // Delete row
//    				$q = "begin delete_order_row(:corder_id, :crow_num, :cquantity); end;";
//    				$stid = $db->parseQuery($q);
//    				oci_bind_by_name($stid, ':corder_id', $_POST['order_id']);
//    				oci_bind_by_name($stid, ':crow_num', $row_num);
//    				oci_execute($stid); // delete row
//    			} else { // Update quantity
//    				$q = "begin update_order_row_quantity(:corder_id, :crow_num, :cquantity); end;";
//    				$stid = $db->parseQuery($q);
//    				oci_bind_by_name($stid, ':cquantity', $_POST['quantity_'.$i]);
//    				oci_bind_by_name($stid, ':crow_num', $row_num);
//    				oci_bind_by_name($stid, ':corder_id', $_POST['order_id']);
//    				oci_execute($stid);  // executes and commits
//    			}
//    		} else { // Doesn't exist - create row
//    			$q = "begin insert_order_row(:corder_id, :crow_num, :cp_id, :cquantity); end;";
//    			$stid = $db->parseQuery($q);
//    			oci_bind_by_name($stid, ':corder_id', $_POST['order_id']);
//    			$new_row = $last_row + 1;
//    			oci_bind_by_name($stid, ':crow_num', $new_row);
//    			oci_bind_by_name($stid, ':cp_id', $_POST['p_id_'.$i]);
//    			oci_bind_by_name($stid, ':cquantity', $_POST['quantity_'.$i]);
//    			oci_execute($stid);  // executes and commits
//    		}
//    		$i++;
//    	}
//    }

}
