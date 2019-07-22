<?php
$rando = rand(11000000000,19999999999);
?>
<!DOCTYPE html>
<html lang="en">

  <head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
	<script defer src="https://use.fontawesome.com/releases/v5.0.8/js/all.js"></script>


    <title><?php echo $housename; ?></title>

    <!-- Bootstrap core CSS -->

<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <!-- Custom styles for this template -->
    <style>
      body {padding-top: 54px;}
      @media (min-width: 992px){body {padding-top: 56px;}}
      .borderless td, .borderless th {border: none;}
      pre {
    display: inline;
    margin: 0;
}
    </style>
  </head>

  <body>

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
      <div class="container">
        <a class="navbar-brand" href="#"><i class="fas fa-home fa-1x"></i>&nbsp;&nbsp;<?php echo $housename; ?></a>

      </div>
    </nav>

    <!-- Page Content -->
    <div class="container">
      <div class="row">
        <div class="col-lg-12">
          <p>
          	<form name="myform" action="index.php" onsubmit="return validateForm()" >
 

 				 <select name="Sender">
    					<option value="<?php echo $admin0; ?>">Admin</option>
    					<option value="<?php echo $nonadmin; ?>">User</option>
    					<option value="<?php echo "+".$rando; ?>">Rando</option>
  				</select>
				&nbsp;&nbsp;<input type="text" name="Body">
				&nbsp;&nbsp;<input type="submit" value="Send">
				&nbsp;&nbsp;Send text: <input type="checkbox" name="usetwilio">&nbsp;&nbsp;Fire hooks: <input type="checkbox" name="fire">&nbsp;&nbsp;Debug: <input type="checkbox" name="debug"><br><hr>
		</form>
