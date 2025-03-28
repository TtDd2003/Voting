<!DOCTYPE html>
<html>
<head>
	<title>Election Results</title>
	<link rel="stylesheet" type="text/css" href="Home.css">
	<link rel="stylesheet" type="text/css" href="bootstrap.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />

	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
</head>
<body>
	<h1 class="text-center bg-dark text-light p-3 m-3"><em><ins>Result</ins></em> </h1>
	<div class="m-auto border col-5">
		<form method="POST" action=""> 
			<h2 class="text-center">
				Enter Election ID:
				<input type="text" name="election_id" required class="form-control col-5 text-center m-auto"> <!-- Using POST to pass the election ID -->
			</h2>
			<input type="submit" name="submit" value="Show Result" class="btn btn-primary m-auto">
		</form>
	</div>
<?php
   include("conn.php");

  if (isset($_POST['election_id']) && is_numeric($_POST['election_id'])) {
    $electionID = mysqli_real_escape_string($conn, $_POST['election_id']);

    $sql = "SELECT * FROM election WHERE ID = '$electionID'";
    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        ?>
        <div id="head" class="m-auto p-2">
            <h2 class="text-center"><b><ins>Election Results for <?php echo htmlspecialchars($row['Topic']); ?></ins></b></h2>
            <h5 class="text-center"><b>Election ID: <?php echo $row['ID']; ?></b></h5>
            <h5 class="text-center"><em>Total Candidates: <?php echo $row['Members']; ?></em></h5>
        </div>

        <?php

        $totalVotesQuery = "SELECT COUNT(*) AS totalVotes FROM count WHERE ElectionID = '$electionID'";
        $totalVotesResult = mysqli_query($conn, $totalVotesQuery);
        $totalVotes = mysqli_fetch_assoc($totalVotesResult)['totalVotes'];
        ?>

        <h5 class="text-center"><b>Total Votes: <?php echo $totalVotes; ?></b></h5>

        <?php
    } else {
        echo "The requested election does not exist or has been removed.";
        exit();
    }

    $joinQuery = "
    SELECT c.Name, c.Party, c.Image, 
       COALESCE(COUNT(DISTINCT v.UserID), 0) AS VoteCount
       FROM candidate c
     LEFT JOIN count v ON v.CandidateID = c.ID AND v.ElectionID = '$electionID'
     WHERE FIND_IN_SET(c.ID, (SELECT Candidates FROM election WHERE ID = '$electionID'))
     GROUP BY c.ID
     ORDER BY VoteCount DESC";


    $statement = mysqli_query($conn, $joinQuery);

    if ($statement && mysqli_num_rows($statement) > 0) {
        echo '<div id="body">';

        $Candidate = mysqli_fetch_assoc($statement);
        echo '<div class="card bg-light mb-3">';
        echo '<img src="' . htmlspecialchars($Candidate['Image']) . '" height="300px" width="300px" class="img img-fluid m-auto">';
        echo '<h4 class="text-center">' . htmlspecialchars($Candidate['Name']) . '</h4>';
        echo '<h5 class="text-center">Party: ' . htmlspecialchars($Candidate['Party']) . '</h5>';
        echo '<h5 class="text-center"><i class="fa-solid fa-thumbs-up"></i>&nbsp; ' . $Candidate['VoteCount'] . ' votes</h5>';
        echo '<h5 class="text-center"><b><ins>Winner!</ins></b></h5>';
        echo '</div>';

        echo '</div>';
    } else {
        echo "No candidates or votes found for this election.";
    }
} else {
    echo "Invalid election ID or missing input.";
}
?>


</body>
</html>
