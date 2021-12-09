<?php
 error_reporting(0);
  // configuration data
  // must use your own id and key with no extra whitespace
  $api = "https://api.unleashedsoftware.com/";
  $apiId = "a87104fe-cdf5-4ce1-9888-b58cc0fe850e";
  $apiKey = "iwdMfK38tbMiyLl6pCtijrDqU5kMnXDlssafxry8JmtsQsdmdmvL6XmQSLRqLB7WJcK7K6Hdu17cdSAW5w==";

  // Get the request signature:
  // Based on your API id and the request portion of the url
  // - $request is only any part of the url after the "?"
  // - use $request = "" if there is no request portion
  // - for GET $request will only be the filters eg ?customerName=Bob
  // - for POST $request will usually be an empty string
  // - $request never includes the "?"
  // Using the wrong value for $request will result in an 403 forbidden response from the API
  function getSignature($request, $key) {
    return base64_encode(hash_hmac('sha256', $request, $key, true));
  }

  // Create the curl object and set the required options
  // - $api will always be https://api.unleashedsoftware.com/
  // - $endpoint must be correctly specified
  // - $requestUrl does include the "?" if any
  // Using the wrong values for $endpoint or $requestUrl will result in a failed API call
  function getCurl($id, $key, $signature, $endpoint, $requestUrl, $format) {
    global $api;

    $curl = curl_init($api . $endpoint . $requestUrl);
    curl_setopt($curl, CURLOPT_FRESH_CONNECT, true);
    curl_setopt($curl, CURLINFO_HEADER_OUT, true);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-Type: application/$format",
          "Accept: application/$format", "api-auth-id: $id", "api-auth-signature: $signature"));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_TIMEOUT, 20);
    // these options allow us to read the error message sent by the API
    curl_setopt($curl, CURLOPT_FAILONERROR, false);
    curl_setopt($curl, CURLOPT_HTTP200ALIASES, range(400, 599));

    return $curl;
  }

  // GET something from the API
  // - $request is only any part of the url after the "?"
  // - use $request = "" if there is no request portion
  // - for GET $request will only be the filters eg ?customerName=Bob
  // - $request never includes the "?"
  // Format agnostic method.  Pass in the required $format of "json" or "xml"
  function get($id, $key, $endpoint, $request, $format) {
    $requestUrl = "";
    if (!empty($request)) $requestUrl = "?$request";

    try {
      // calculate API signature
      $signature = getSignature($request, $key);
      // create the curl object
      $curl = getCurl($id, $key, $signature, $endpoint, $requestUrl, $format);
      // GET something
      $curl_result = curl_exec($curl);
      error_log($curl_result);
      curl_close($curl);
      return $curl_result;
    }
    catch (Exception $e) {
      error_log('Error: ' + $e);
    }
  }

  // GET in XML format
  // - gets the data from the API and converts it to an XML object
  function getXml($id, $key, $endpoint, $request) {
    // GET it
    $xml = get($id, $key, $endpoint, $request, "xml");
    // Convert to XML object and return
    return new SimpleXMLElement($xml);
  }

  // GET in JSON format
  // - gets the data from the API and converts it to an stdClass object
  function getJson($id, $key, $endpoint, $request) {
    // GET it, decode it, return it
    return json_decode(get($id, $key, $endpoint, $request, "json"));
  }

  // Example method: GET customer list in xml or json
  function getCustomers($format) {
    global $apiId, $apiKey;

    if ($format == "xml")
      return getXml($apiId, $apiKey, "Customers", "");
    else
      return getJson($apiId, $apiKey, "Customers", "");
  }

  // Example method: GET order list in xml or json
  function getSalesOrders($format) {
    global $apiId, $apiKey;

    if ($format == "xml")
      return getXml($apiId, $apiKey, "SalesOrders", "");
    else
      return getJson($apiId, $apiKey, "SalesOrders", "");
  }

  // Example method: GET shipment list in xml or json
  function getSalesShipments($format) {
    global $apiId, $apiKey;

    if ($format == "xml")
      return getXml($apiId, $apiKey, "SalesShipments", "");
    else
      return getJson($apiId, $apiKey, "SalesShipments", "");
  }

  // -------------------------------------------------------
  // TEST all methods and show the outputs
  // -------------------------------------------------------


    function testGetSalesOrders() {
        $json = getSalesOrders("json");

        echo "
        <table class='sales-orders'>
            <caption class='fixed-caption'><h1>Sales Orders<h1></caption>
            <thead class='fixed-thead'>
                <tr>
                    <th>Order Number</th>
                    <th>Customer</th>
                    <th>Ordered</th>
                    <th>Required</th>
                    <th>PO Number</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
        ";   
        foreach ($json->Items as $order) {
            if ($order->OrderStatus != "Completed" && $order->Warehouse->WarehouseCode == "W1") {
                $ordernumber = $order->OrderNumber;
                $date = $order->OrderDate;
                $reqdate = $order->RequiredDate;
                $status = $order->OrderStatus;
                $ref = $order->CustomerRef;
                $name =$order->Customer->CustomerName;
                $reqresult = str_replace( array("/", "Date", "(", ")"), '', $reqdate);
                $reqresultdate = $reqresult / 1000;
                $req = date("D d/m", $reqresultdate);
                if ($date != null) {
                    $result = str_replace( array("/", "Date", "(", ")"), '', $date);
                    $resultdate = $result / 1000;
                    $orderdate = date("D d/m", $resultdate);
                } else {
                    $orderdate = "N/A";
                }
                echo "
                <tr>
                    <td>$ordernumber</td>
                    <td>$name</td>
                    <td>$orderdate</td>
                    <td>$req</td>
                    <td>$ref</td>
                    <td><button type='button' class='$status button'>$status</button></td>
                </tr>
                ";
            }
        }
        if ($json == null) {
          echo "
          <tr>
            <td colspan='6' class='text-center'><button type='button' class='alert button'>ERROR WITH FEED! (not Trent's fault though)</button></td>
          </tr>
          ";
        }
        echo "
            </tbody>
        </table>"; 
        echo "<div class='source-elements hide'>";
        foreach ($json->Items as $order) {
            if ($order->OrderStatus != "Completed") {
                $ordernumber = $order->OrderNumber;
                $name =$order->Customer->CustomerName;
                echo "<div class='sourcetext $ordernumber'>$name</div>";
            }
        }
        echo "</div>";
    }

    function testGetSalesShipments() {
        $json = getSalesShipments("json");
        echo "
        <table class='sales-shipments'>
            <caption class='fixed-caption'><h1>Sales Shipments<h1></caption>
            <thead class='fixed-thead'>
                <tr>
                    <th>Shipment Number</th>
                    <th class='shipment-customer-name'>Customer</th>
                    <th>Dispatch Date</th>
                    <th>Tracking Number</th>
                    <th>Shipping Company</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
        ";
        // echo json_encode($json);
        foreach ($json->Items as $shipment) {
            if ($shipment->ShipmentStatus != "Dispatched" && $shipment->ShipmentStatus != "Deleted") {
                $shipmentnumber = $shipment->ShipmentNumber;
                $date = $shipment->DispatchDate;
                $trackingnumber = $shipment->TrackingNumber;
                $ordernumber = $shipment->OrderNumber;
                $status = $shipment->ShipmentStatus;
                if ($date != null) {
                    $result = str_replace( array("/", "Date", "(", ")"), '', $date);
                    $resultdate = $result / 1000;
                    $dispatchdate = date("D d/m", $resultdate);
                } else {
                    $dispatchdate = "N/A";
                }
                if ($shipment->ShippingCompany->Name == null) {
                    $shippingcompany = "Magic Carpet!";
                    $shippingguid = "magic-carpet";
                } else {
                    $shippingcompany = $shipment->ShippingCompany->Name;
                    $shippingguid = $shipment->ShippingCompany->Guid;
                }

                if ($shipment->ShippingCompany->Guid == "0b9b7721-f606-4bfd-b424-25455c1241b4") {
                  if ($shipment->TrackingNumber == null) {
                    $trackingnumber = "0402 794 437";
                  } else {
                    $trackingnumber = $shipment->TrackingNumber;
                  }
                }
                if ($shipment->ShippingCompany->Guid == "a03ef2d9-f865-452e-8d91-c39e75a974b9") {
                  if ($shipment->TrackingNumber == null) {
                    $trackingnumber = "0411 532 195";
                  } else {
                    $trackingnumber = $shipment->TrackingNumber;
                  }
                }
                if ($shipment->ShippingCompany->Guid == "62987aee-a1cb-4581-88b4-22c646c3cafc") {
                  if ($shipment->TrackingNumber == null) {
                    $trackingnumber = "1300 652 833";
                  } else {
                    $trackingnumber = $shipment->TrackingNumber;
                  }
                }
                if ($shipment->ShippingCompany->Guid == "77a7ca28-c52e-4a85-b586-d6a6ad0e52a9") {
                  if ($shipment->TrackingNumber == null) {
                    $trackingnumber = "000";
                  } else {
                    $trackingnumber = $shipment->TrackingNumber;
                  }
                }
                
                echo "
                <tr>
                    <td>$shipmentnumber</td>
                    <td class='replacetext $ordernumber'>replacetext$ordernumber</td>
                    <td>$dispatchdate</td>
                    <td>$trackingnumber</td>
                    <td><button type='button' class='$shippingguid button'>$shippingcompany</button></td>
                    <td><button type='button' class='$status button'>$status</button></td>
                </tr>
                ";
            }
        }
        if ($json == null) {
          echo "
          <tr>
            <td colspan='6' class='text-center'><button type='button' class='alert button'>ERROR WITH FEED! (not Trent's fault though)</button></td>
          </tr>
          ";
        }
        echo "	
            </tbody>
        </table>";
    }

    // testGetCustomers();

    echo '<!doctype html>
    <html class="no-js" lang="en">
        <head>
            <meta charset="utf-8" />
            <meta http-equiv="x-ua-compatible" content="ie=edge">
            <meta name="viewport" content="width=device-width, initial-scale=1.0" />
            <title>Truclean Order Updates</title>
            <link rel="stylesheet" href="dist/assets/css/app.css" />
        </head>
        <body onload="startTime()">
            <div id="clock" class="clock"></div>
            <div class="grid-x">
                <div class="cell small-6 right-border">';
                    testGetSalesOrders();
                echo'</div>
                <div class="cell small-6">';
                    testGetSalesShipments();
                echo'</div>
            </div>
            <script src="dist/assets/js/app.js"></script>
            <script>
              function startTime() {
                const today = new Date();
                let h = today.getHours();
                let m = today.getMinutes();
                let s = today.getSeconds();
                m = checkTime(m);
                s = checkTime(s);
                document.getElementById("clock").innerHTML =  h + ":" + m + ":" + s;
                setTimeout(startTime, 1000);
              }

              function checkTime(i) {
                if (i < 10) {i = "0" + i};  // add zero in front of numbers < 10
                return i;
              }
            </script>
        </body>
    </html>
    ';
?>