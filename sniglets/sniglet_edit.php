<?php


require("config.inc.php");
$sniglet = new sniglet();

session_start();


if(!isset($_SESSION['access'])){
    // redirect_to('login.php');
}

if(isset($_GET['func']) && $_GET['func'] == 'logout'){
    die($sniglet->logout());
}

if(isset($_GET['func']) && $_GET['func'] == 'searchSniglet') {
    die(json_encode($sniglet->searchSniglet($_GET['searchTxt'])));
}
elseif (isset($_GET) && $_GET['func'] == 'getStats') {
    die($sniglet->getSnigletStats());

}
elseif (isset($_GET) && $_GET['func'] == 'list') {
    die($sniglet->listAll());

}
elseif (isset($_POST['func']) && $_POST['func'] == 'save') {
    die($sniglet->addSniglet($_POST));

}
elseif (isset($_POST['func']) && $_POST['func'] == 'load') {
    die($sniglet->loadById($_POST['id']));

}
elseif (isset($_GET['func']) && $_GET['func'] == 'searchByAlpha') {
    die($sniglet->searchByAlpha($_GET['letter']));

}else{

 ?>

<html>
<head>
<title>Sniglets for Fun and Profit</title>
    <link rel="stylesheet" href="css/sniglets.css">
    <script src="js/jquery-1.8.3.js"></script>
    <script src="js/sniglets.js"></script>
</head>

<body onload=getSniglet();>
<div id="formArea">
    <div id="listLink"><a href="javascript: list()">List All</a></div>
    <?php
        if($_SESSION['access'] == '10'){
    ?>
    <div id="creds"></div>
    <div id="formHeader">Add A Sniglet</div>
    <form action="sniglet_edit.php" method="POST">
        <legend>Sniglet Term</legend>
        <input id="sniglet_term" type="text" name="sniglet_term" size=30>
        <legend>Phonetic Spelling</legend>
        <input id="sniglet_phonetics" type="text" name="sniglet_phonetics" size="30">
        <legend>Sniglet type</legend>
        <select id="sniglet_type" name="sniglet_type">
            <option value="n.">noun</option>
            <option value="v.">verb</option>
            <option value="adj.">adjective</option>
        </select><br/>
        <textarea id="sniglet_definition" cols="30" rows="4" name="sniglet_definition"></textarea>
        <br/>
        <input id="savebtn" type="button" onclick="save_sniglet()" value="Save Sniglet">
    </form>

    <?php

        }else{
           echo'<div id="login"></div><div id="formHeader">Sniglets By Scott</div>';
           echo '<div id="filler"></div>';
        }
     ?>


</div>

<div id="statistics"></div>
<div id="links"><?php echo $sniglet->makeLinks(); ?></div>

<div id ="SnigletTxt"></div>
<div id="listArea"></div>
<div id="resultArea"></div>


</body>
</html>
<?php
}
?>