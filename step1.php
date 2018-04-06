<?php

// make sure browsers see this page as utf-8 encoded HTML
header('Content-Type: text/html; charset=utf-8');

$limit = 10;
$query = isset($_REQUEST['q']) ? $_REQUEST['q'] : false;
$results = false;

//edit
$selectOption = $_GET["taskOption"];
?>

<?php
if ($query)
{
  // The Apache Solr Client library should be on the include path
  // which is usually most easily accomplished by placing in the
  // same directory as this script ( . or current directory is a default
  // php include path entry in the php.ini)
  require_once('Apache/Solr/Service.php');

  // create a new solr service instance - host, port, and webapp
  // path (all defaults in this example)
  $solr = new Apache_Solr_Service('localhost', 8983, '/solr/myexample/');

  // if magic quotes is enabled then stripslashes will be needed
  if (get_magic_quotes_gpc() == 1)
  {
    $query = stripslashes($query);
  }

  // in production code you'll always want to use a try /catch for any
  // possible exceptions emitted  by searching (i.e. connection
  // problems or a query parsing error)
  try
  {
    if($selectOption == "1"){
      // Original Ranking Algorithm
      $results = $solr->search($query, 0, $limit);
    }
    if($selectOption == "2"){
      // Page Rank Algorithm
      $additionalParameters = array(
      'sort' => 'pageRankFile.txt desc'
      );
      $results = $solr->search($query, 0, $limit, $additionalParameters);  
    }
    
  }
  catch (Exception $e)
  {
    // in production you'd probably log or email this error to an admin
    // and then show a special message to the user but for this example
    // we're going to show the full exception
    die("<html><head><title>SEARCH EXCEPTION</title><body><pre>{$e->__toString()}</pre></body></html>");
  }
}

?>
<html>
  <head>
    <title>PHP Solr Client Example</title>
  </head>
  <body>
    <form  accept-charset="utf-8" method="get">
      <label for="taskOption">RankAlgo:</label>
      <select name="taskOption">
        <option value="1">Original</option>
        <option value="2">PageRank</option>
      </select>
      <label for="q">Search:</label>
      <input id="q" name="q" type="text" value="<?php echo htmlspecialchars($query, ENT_QUOTES, 'utf-8'); ?>"/>
      <input type="submit"/>
    </form>
<?php

// display results
if ($results)
{
  $total = (int) $results->response->numFound;
  $start = min(1, $total);
  $end = min($limit, $total);
?>
    <div>Results <?php echo $start; ?> - <?php echo $end;?> of <?php echo $total; ?>:</div>
    <ol>
<?php
  // iterate result documents
  foreach ($results->response->docs as $doc)
  {
?>
      <li>
        <table style="border: 1px solid black; text-align: left">
<?php

    // Create an array to store id, title, field and description
    // $data[0] stores title
    // $data[1] stores URL
    // $data[2] stores ID
    // $data[3] stores Description
    $data = {NULL, NULL, NULL, NULL};

    // iterate document fields / values
    foreach ($doc as $field => $value)
    {
?>

    <!-- id -->
    <?php
    if( $field == 'id'){
    ?>
      <tr>
            <th><?php echo htmlspecialchars('id = ', ENT_NOQUOTES, 'utf-8'); ?></th>
            <td><?php echo htmlspecialchars($value, ENT_NOQUOTES, 'utf-8'); ?></td>
          </tr>
    <?php
    }
    ?>

    <!-- title -->
    <?php
    if( $field == 'title'){
    ?>
      <tr>
            <th><?php echo htmlspecialchars("title = ", ENT_NOQUOTES, 'utf-8'); ?></th>
            <td><?php echo htmlspecialchars($value, ENT_NOQUOTES, 'utf-8'); ?></td>
          </tr>
    <?php
    }
    ?>

    <!-- url -->
    <?php
    if($field == 'og_url'){
    ?>
      <tr>
            <th><?php echo htmlspecialchars("url = ", ENT_NOQUOTES, 'utf-8'); ?></th>
            <td><?php echo htmlspecialchars($value, ENT_NOQUOTES, 'utf-8'); ?></td>
          </tr>
    <?php
    }
    ?>

    <!-- description -->
    <?php
    if($field == 'og_description'){
    ?>
      <tr>
            <th><?php echo htmlspecialchars("description = ", ENT_NOQUOTES, 'utf-8'); ?></th>
            <td><?php echo htmlspecialchars($value, ENT_NOQUOTES, 'utf-8'); ?></td>
          </tr>
    <?php
    }
    ?>

          
<?php
    }
?>
        </table>
      </li>
<?php
  }
?>
    </ol>
<?php
}
?>
  </body>
</html>

