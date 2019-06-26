<?php
session_start();
error_reporting(0);
include('includes/config.php');
if(strlen($_SESSION['alogin'])==0)
    {   
header('location:index.php');
}
else{ 

if(isset($_POST['return'])){
    $rid=intval($_GET['rid']);
    $bookid=intval($_GET['BookID']);
    $fine=$_POST['fine'];
    $rstatus=1;
    date_default_timezone_set('Asia/Jakarta');
    $tglkembali = date('Y-m-d');
    $sql="update tblissuedbookdetails set fine=:fine,ReturnDate='$tglkembali', RetrunStatus=:rstatus where id=:rid";
    $query = $dbh->prepare($sql);
    $query->bindParam(':rid',$rid,PDO::PARAM_STR);
    $query->bindParam(':fine',$fine,PDO::PARAM_STR);
    $query->bindParam(':rstatus',$rstatus,PDO::PARAM_STR);
    $query->execute();
    $sql="update tblbooks set tersedia=tersedia+1 where id=$bookid";
    $query = $dbh->prepare($sql);
    $query->execute();
    $_SESSION['msg']="Buku Berhasil Dikembalikan";
    header('location:manage-issued-books.php');
}

//fungsi buat cek denda
    if(isset($_POST['denda'])){
        date_default_timezone_set('Asia/Jakarta');
        $tglkembali = date('Y-m-d');
        $rid=intval($_GET['rid']);
            $sql="select batas_pengembalian from tblissuedbookdetails where id=:rid";
            $query = $dbh->prepare($sql);
            $query->bindParam(':rid',$rid,PDO::PARAM_STR);
            $query->execute();
            $results=$query->fetchAll(PDO::FETCH_OBJ);
            if($query->rowCount() > 0){
                foreach($results as $result){
                    $bataskembali=$result->batas_pengembalian;
                }
            }
            $tglkembali=strtotime($tglkembali);
            $bataskembali=strtotime($bataskembali);
            $tglterlambat=$tglkembali-$bataskembali;
        echo '<br>';
        $tglterlambat=floor($tglterlambat/ (60 * 60 * 24));
        $denda=0;
        if($tglterlambat<0)
            echo "Belum Terlambat";
        else{
            $denda=$tglterlambat*1500;
            echo $denda;
        }
        $sql="update tblissuedbookdetails set fine='$denda' where id=:rid";
        $query = $dbh->prepare($sql);
        $query->execute();

    }
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>Perpustakaan UPN</title>
    <!-- BOOTSTRAP CORE STYLE  -->
    <link href="assets/css/bootstrap.css" rel="stylesheet" />
    <!-- FONT AWESOME STYLE  -->
    <link href="assets/css/font-awesome.css" rel="stylesheet" />
    <!-- CUSTOM STYLE  -->
    <link href="assets/css/style.css" rel="stylesheet" />
    <!-- GOOGLE FONT -->
    <link href='http://fonts.googleapis.com/css?family=Open+Sans' rel='stylesheet' type='text/css' />
        <style>
    .divider
    {
        height: 5px;
    }
    </style>
<script>

function getstudent() {
$("#loaderIcon").show();
jQuery.ajax({
url: "get_student.php",
data:'studentid='+$("#studentid").val(),
type: "POST",
success:function(data){
$("#get_student_name").html(data);
$("#loaderIcon").hide();
},
error:function (){}
});
}


function getbook() {
$("#loaderIcon").show();
jQuery.ajax({
url: "get_book.php",
data:'bookid='+$("#bookid").val(),
type: "POST",
success:function(data){
$("#get_book_name").html(data);
$("#loaderIcon").hide();
},
error:function (){}
});
}

</script> 
<style type="text/css">
  .others{
    color:red;
}

</style>


</head>
<body>
      <!------MENU SECTION START-->
<?php include('includes/header.php');?>
<!-- MENU SECTION END-->
    <div class="content-wra
    <div class="content-wrapper">
         <div class="container">
        <div class="row pad-botm">
            <div class="col-md-12">
                <h4 class="header-line">Detail Peminjaman Buku</h4>
                
                            </div>

</div>
<div class="row">
<div class="col-md-10 col-sm-6 col-xs-12 col-md-offset-1"">
<div class="panel panel-info">
<div class="panel-heading">
Peminjaman Buku
</div>
<div class="panel-body">
<form role="form" method="post">
<?php 
$rid=intval($_GET['rid']);
$sql = "SELECT tblstudents.FullName,tblbooks.BookName,tblbooks.ISBNNumber,tblissuedbookdetails.IssuesDate,tblissuedbookdetails.ReturnDate,tblissuedbookdetails.id as rid,tblissuedbookdetails.fine,tblissuedbookdetails.RetrunStatus from  tblissuedbookdetails join tblstudents on tblstudents.StudentId=tblissuedbookdetails.StudentId join tblbooks on tblbooks.id=tblissuedbookdetails.BookId where tblissuedbookdetails.id=:rid";
$query = $dbh -> prepare($sql);
$query->bindParam(':rid',$rid,PDO::PARAM_STR);
$query->execute();
$results=$query->fetchAll(PDO::FETCH_OBJ);
$cnt=1;
if($query->rowCount() > 0)
{
foreach($results as $result)
{               ?>                                      
                   



<div class="form-group">
<label>Nama Siswa :</label>
<?php echo htmlentities($result->FullName);?>
</div>

<div class="form-group">
<label>Judul Buku:</label>
<?php echo htmlentities($result->BookName);?>
</div>


<div class="form-group">
<label>ISBN :</label>
<?php echo htmlentities($result->ISBNNumber);?>
</div>

<div class="form-group">
<label>Waktu Pinjam :</label>
<?php echo htmlentities($result->IssuesDate);?>
</div>


<div class="form-group">
<label>Waktu Kembali :</label>
<?php if($result->ReturnDate=="")
                                            {
                                                echo htmlentities("Belum Kembali");
                                            } else {


                                            echo htmlentities($result->ReturnDate);
}
                                            ?>
</div>

<div class="form-group">
<label>Denda (Rp) :</label>
<?php 
if($result->fine=="")
{?>
<input class="form-control" type="text" name="fine" id="fine" />
<div class="divider"></div>
<button  type="submit" name="denda" id="denda" class="btn btn-info">Cek Denda </button>
<?php }else {
echo htmlentities($result->fine);
}
?>
</div>
 <?php if($result->RetrunStatus==0){?>

<button type="submit" name="return" id="submit" class="btn btn-info">Kembalikan Buku </button>

 </div>

<?php }}} ?>
                                    </form>
                            </div>
                        </div>
                            </div>

        </div>
   
    </div>
    </div>
     <!-- CONTENT-WRAPPER SECTION END-->
  <?php include('includes/footer.php');?>
      <!-- FOOTER SECTION END-->
    <!-- JAVASCRIPT FILES PLACED AT THE BOTTOM TO REDUCE THE LOADING TIME  -->
    <!-- CORE JQUERY  -->
    <script src="assets/js/jquery-1.10.2.js"></script>
    <!-- BOOTSTRAP SCRIPTS  -->
    <script src="assets/js/bootstrap.js"></script>
      <!-- CUSTOM SCRIPTS  -->
    <script src="assets/js/custom.js"></script>

</body>
</html>
<?php } ?>
