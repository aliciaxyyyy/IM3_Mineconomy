<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Mineral Values - Chart</title>
	<link rel="stylesheet" href="../css/style.css">
	<style>
		body {
			background-image: url("../images/mine-backdrop.jpg") !important;
			background-size: cover;
			background-position: center center;
			background-repeat: no-repeat;
			background-attachment: fixed;
		}
	</style>
	<!-- Chart.js CDN -->
	<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body data-period="1">
	<!-- Logo -->
	<a href="../index.html">
		<img src="../images/torchglow.png" alt="Mine Logo" class="torch-logo">
	</a>

	<!-- Help Tooltip -->
	<div class="help-tooltip">
		<div class="help-icon">?</div>
		<div class="help-content">
			<h4>How to use</h4>
			<ul>
				<li>Drag metals from the container</li>
				<li>Drop them into the drop container</li>
				<li>Press "Go" to view charts with your selected metals</li>
				<li>Click checkpoints on the railway to select different time period</li>
			</ul>
		</div>
	</div>

	<!-- Drag & Drop Section -->
	<div class="minerals-container">
		<p class="minerals-text">Drag and drop metals into the drop container:</p>
		<img id="gold" src="../images/Gold.png" alt="Gold" class="draggable-item" draggable="true" data-mineral="gold">
		<img id="silver" src="../images/Silver.png" alt="Silver" class="draggable-item" draggable="true" data-mineral="silver">
		<img id="platinum" src="../images/Platinum.png" alt="Platinum" class="draggable-item" draggable="true" data-mineral="platinum">
	</div>

	<!-- Containers Wrapper for side-by-side layout -->
	<div class="containers-wrapper">
		<!-- Drop Container (left side) -->
		<div class="drop-container">
			<h2 class="drop-title">Drop container</h2>
			<div class="drop-grid" id="dropContainer">
				<div class="drop-cell" id="cell1"></div>
				<div class="drop-cell" id="cell2"></div>
				<div class="drop-cell" id="cell3"></div>
				<div class="drop-cell" id="cell4"></div>
			</div>
			<div class="go-button-minerals-container">
				<button class="go-button-minerals">Go</button>
			</div>
		</div>

		<!-- Chart Section (right side) -->
		<div class="main-container">
			<div class="page-content">
				<h1 class="page-title">Precious Metals Chart</h1>

				<!-- Zeitraum-Auswahl mit Minecart-Zugschiene -->
				<div class="railway-container">
					<div class="timelabel">Select Time Period:</div>
					<div class="railway-track">
						<div class="rail-line"></div>

						<!-- Checkpoints -->
						<div class="checkpoint" data-period="1" id="checkpoint-1">
							<div class="checkpoint-marker"></div>
							<div class="checkpoint-label">Today</div>
						</div>
						<div class="checkpoint" data-period="7" id="checkpoint-7">
							<div class="checkpoint-marker"></div>
							<div class="checkpoint-label">7 Days</div>
						</div>
						<div class="checkpoint" data-period="30" id="checkpoint-30">
							<div class="checkpoint-marker"></div>
							<div class="checkpoint-label">30 Days</div>
						</div>

						<!-- Minecart -->
						<div class="minecart" id="minecart">
							<div class="cart-body"></div>
							<div class="cart-wheels">
								<div class="wheel wheel-left"></div>
								<div class="wheel wheel-right"></div>
							</div>
						</div>
					</div>
				</div>

				<!-- Chart Container -->
				<div class="chart-container">
					<canvas id="metalsChart"></canvas>
				</div>
			</div>
		</div>
	</div> <!-- End containers-wrapper -->

	<!-- Drag & Drop, Go-Button, Minecart logic -->
	<script src="../js/script.js"></script>
</body>
</html>