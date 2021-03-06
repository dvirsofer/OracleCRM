<?php

@require_once('help_functions.php');
@require_once('database/Warehouse.php');

@session_start();

$sort_info = "";
$sort_type = "";
$warehouse_id = "";

$warehouses = Warehouse::getWarehouseList();

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $warehouse_id = $_POST['warehouse_id'];
    $sort_type = $_POST['sort_type'];
    $sort_info = $_POST['sort_info'];

    $warehouseInventory = Warehouse::getWarehouseInventory($warehouse_id, $sort_info, $sort_type);

}

?>

<?php include_once('parts/head.php'); ?>

<body>

<div class="wrapper">

    <?php include_once('parts/sidebar.php'); ?>

    <div class="main-panel">

        <?php include_once('parts/nav_warehouse.php'); ?>

        <div class="content">
            <div class="container-fluid">
                <div class="row">
                    <!-- Main content start here -->

                    <form method="post" action="warehouse_inventory.php">
                        <div class="row">
                            <div class="form-group col-md-12">
                                <label for="warehouse_id">Select warehouse</label>
                                <select class="form-control" id="warehouse_id" name="warehouse_id">
                                    <option value="0">select warehouse</option>
                                    <?php foreach($warehouses as $warehouse) { ?>
                                        <option value="<?php echo($warehouse->WAREHOUSE_ID) ?>"<?php echo($warehouse_id == $warehouse->WAREHOUSE_ID ? ' selected=selected' : '');?>><?php
                                            echo($warehouse->WAREHOUSE_NAME) ?></option>
                                    <?php } ?>
                                    </select>
                            </div>
                            <div class="form-group col-md-4">
                                <label for="sort_info">Sort By</label>
                                <select class="form-control" id="sort_info" name="sort_info">
                                    <option value="0">select sort info</option>
                                    <option <?php if($sort_info == "p_id") { ?> selected="selected" <?php } ?> value="p_id">Id</option>
                                    <option <?php if($sort_info == "description") { ?> selected="selected" <?php } ?> value="description">Description</option>
                                    <option <?php if($sort_info == "quantity") { ?> selected="selected" <?php } ?> value="quantity">Quantity</option>
                                </select>
                            </div>
                            <div class="form-group col-md-4">
                                <label for="sort_type">Sort</label>
                                <select class="form-control" id="sort_type" name="sort_type">
                                    <option value="0">select sort</option>
                                    <option <?php if($sort_type == "1") { ?> selected="selected" <?php } ?> value="1">asc</option>
                                    <option <?php if($sort_type == "2") { ?> selected="selected" <?php } ?> value="2">desc</option>
                                </select>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </form>

                    <?php if ($_SERVER["REQUEST_METHOD"] == "POST") {?>

                        <h3>All inventory of warehouse</h3>
                        <table class="table table-hover table-striped">
                            <thead>
                            <th>Description</th>
                            <th>Quantity</th>
                            </thead>
                            <tbody>
                            <?php foreach($warehouseInventory as $warehouseInfo): ?>
                                <tr>
                                    <td><?php echo($warehouseInfo->P_ID); ?></td>
                                    <td><?php echo($warehouseInfo->DESCRIPTION); ?></td>
                                    <td><?php echo($warehouseInfo->QUANTITY); ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>

                    <?php } ?>

                </div>
            </div>
        </div>

    </div>
</div>


</body>


<?php include_once('parts/bottom.php'); ?>


</html>
