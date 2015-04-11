<!doctype html>
<html>
  <body>
    <b>Enter the customer to see all of its order information:</b>
    <FORM NAME = "Customer" METHOD="POST" ACTION="customer_orders_results.php">
      <INPUT TYPE = "TEXT" VALUE="Customer Name" NAME = "customer">
      <INPUT TYPE = "submit" VALUE = "submit" Name = "submit">
    </FORM>
  </body>
</html>

<!doctype html>
<html>
  <body>
    <?php
      if (isset($_POST['submit'])) {
        $customer = $_POST['customer'];
        echo "<b>Order Information for the Customer \"$customer\":</b>";
        $c = oci_connect('ormond', 'testing', '//10.42.220.7:1521/xe');
        if (!$c) { // Ensuring a connection was made
          echo "Unable to connect: " . var_dump(dci_error());
          die();
        }
        $sql =        "SELECT o.order_id, o.order_date, p.product_description, p.product_price, ol.ordered_quantity ";
        $sql = $sql . "FROM customer c, orders o, order_line ol, product p ";
        $sql = $sql . "WHERE c.customer_id = o.customer_id ";
        $sql = $sql . "AND ol.order_id = o.order_id ";
        $sql = $sql . "AND p.product_id = ol.product_id ";
        $sql = $sql . "AND UPPER(c.customer_name) LIKE UPPER(('%{$customer}%')) ";
        $sql = $sql . "ORDER BY o.order_id";
        $stmt = oci_parse($c, $sql) or die ('Can not parse query');
        oci_execute($stmt, OCI_DEFAULT) or die ('Can not execute statement');
        $subtotal = 0;
        $total = 0;
        echo "<div><table border=\"1\" align=\"left\">";
        echo "<tr><th>Order ID</th><th>Order Date</th><th>Product Description</th><th>Product Price</th><th>Ordered Quantity</th><th>Subtotal</th></tr>";
        while(oci_fetch($stmt)) {
          $subtotal = oci_result($stmt, "PRODUCT_PRICE") * oci_result($stmt, "ORDERED_QUANTITY");
          echo "<tr><td>" . oci_result($stmt, "ORDER_ID") . "</td>" .
               "<td>" . oci_result($stmt, "ORDER_DATE") . "</td>" .
               "<td>" . oci_result($stmt, "PRODUCT_DESCRIPTION") . "</td>" .
               "<td>" . oci_result($stmt, "PRODUCT_PRICE") . "</td>" .
               "<td>" . oci_result($stmt, "ORDERED_QUANTITY") . "</td>" .
               "<td>$" . $subtotal . "</td></tr>";
          $total = $total + $subtotal;
        }
        echo "<tr><td colspan=\"5\"><b>Total</b></td><td><b>$" . $total . "</b></td></tr>";
        echo "</table></div>";
        oci_free_statement($stmt);
        oci_close($c);
      }
    ?>
  </body>
</html>
