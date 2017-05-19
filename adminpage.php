<!DOCTYPE html>
<html>
<head>
	<title>English Bookstore - Admin page </title>
	<link rel="stylesheet" type="text/css" href="css/style.css">
	<link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css?family=Quicksand" rel="stylesheet">
</head>

<body>

	<header>
		<section id="admin-navbar">
			<article class="nav">
				<ul>
					<li><a href="storefront.php"><img src="img/logo-white.png"></a></li>
					<button class="log-out-btn">Log out</button>
					<li class="align-right"><a href="storefront.php">Go to storefront</a></li>
				</ul>
			</article>
		</section>
	</header>

	<main>

		<section id="add-new-book">
			<!-- Trigger/Open The Modal -->
			<button id="add-book-btn" class="new-book-btn"><span>Add a new book</span></button>
			<!-- The Modal -->
			<article id="add-book-modal" class="modal">
			  <!-- Modal content -->
			  <article class="add-book-modal-content">
					<article class="add-book-modal-header">
				    <span class="close">&times;</span>
				    <h3>Add a new book</h3>
				  </article>
				  <article class="add-book-modal-body">
						<input id="isbn-input" type="text" placeholder="Enter ISBN" />
						<button id="scan-isbn-btn">Scan ISBN</button>
						<input type="text" placeholder="Enter amount" /> <span>Kr.</span>
						<article class="dropdown">
						  <button class="dropbtn">Choose category</button>
						  <article class="dropdown-content">
						    <a href="#">Action & Adventure</a>
						    <a href="#">Business</a>
						    <a href="#">Cooking</a>
								<a href="#">DIY</a>
								<a href="#">Health & Fitness</a>
								<a href="#">Romance</a>
								<a href="#">Travel</a>
								<a href="#">Sci-fi</a>
								<a href="#">Sports</a>
						  </article>
						</article>
						<button id="check-book-btn">Check</button>
						<button id="reset-btn">Reset</button>
						<p>Showing book title and author from Goodreads API</p>
						<button class="add-btn">ADD BOOK</button>
				  </article>
			  </article>
			</article>
		</section>

		<section id="search-bar-admin">
			<input type="text" id="myInput" onkeyup="myFunction()" placeholder="Search books by ISBN..">
		</section>

		<section id="books-table">
			<table id="myTable">
			  <tr class="header">
					<th style="width:20%;">ISBN</th>
					<th style="width:25%;">Title</th>
					<th style="width:25%;">Author</th>
					<th style="width:10%;">Price</th>
			    <th style="width:10%;">Reserved</th>
			    <th style="width:10%;">Delete</th>
			  </tr>
			  <tr>
					<td>000000123</td>
			    <td>Alfreds Futterkiste</td>
			    <td>Germany</td>
					<td>50</td>
					<td>check</td>
					<td>trash</td>
			  </tr>
			  <tr>
					<td>123456789</td>
			    <td>Berglunds snabbkop</td>
			    <td>Sweden</td>
					<td>50</td>
					<td>check</td>
					<td>trash</td>
			  </tr>
			  <tr>
					<td>123456789</td>
			    <td>Island Trading</td>
			    <td>UK</td>
					<td>50</td>
					<td>check</td>
					<td>trash</td>
			  </tr>
			  <tr>
					<td>123456789</td>
			    <td>Koniglich Essen</td>
			    <td>Germany</td>
					<td>50</td>
					<td>check</td>
					<td>trash</td>
			  </tr>
			</table>
		</section>
	</main>

	<footer>
		<section id="admin-footer">
			<p>Frederiks Allé 53, 8000 Aarhus C</p>
			<p>52 90 28 35</p>
			<p>books@stofanet.dk</p>
			<p>Wednesday to Friday - 2:30 PM to 5:30 PM</p>
			<p>Saturday - 11:00 AM to 1:30 PM </p>
		</section>
	</footer>

	<script type="text/javascript" src="js/script.js"></script>

</body>
</html>
