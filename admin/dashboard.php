<!-- Template Files here -->
 <?php 
    include 'partials/header.php'; 
    include 'partials/side-bar.php'; 
    include '../functions.php';

    $con = connectDatabase();
    if (!$con || $con->connect_error) {
        die("Database connection failed: " . $con->connect_error);
    }

    $subjectCount = fetchTotalSub($con);
    $studentCount = fetchTotalStud($con);
    $numberFailed = fetchFailedStud($con);
    $numberPassed = fetchPassedStud($con);

 ?>
<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 pt-5">    
    <h1 class="h2">Dashboard</h1>        
    
    <div class="row mt-5">
        <div class="col-12 col-xl-3">
            <div class="card border-primary mb-3">
                <div class="card-header bg-primary text-white border-primary">Number of Subjects:</div>
                <div class="card-body text-primary">
                    <h5 class="card-title"><?php echo htmlspecialchars($subjectCount); ?></h5>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-3">
            <div class="card border-primary mb-3">
                <div class="card-header bg-primary text-white border-primary">Number of Students:</div>
                <div class="card-body text-success">
                    <h5 class="card-title"><?php echo htmlspecialchars($studentCount); ?></h5>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-3">
            <div class="card border-danger mb-3">
                <div class="card-header bg-danger text-white border-danger">Number of Failed Students:</div>
                <div class="card-body text-danger">
                    <h5 class="card-title"><?php echo htmlspecialchars($numberFailed); ?></h5>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-3">
            <div class="card border-success mb-3">
                <div class="card-header bg-success text-white border-success">Number of Passed Students:</div>
                <div class="card-body text-success">
                    <h5 class="card-title"><?php echo htmlspecialchars($numberPassed); ?></h5>
                </div>
            </div>
        </div>
    </div>    
</main>
<!-- Template Files here -->
 <?php
    include 'partials/footer.php'; 
 ?>