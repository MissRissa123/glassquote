<?php
// Fetch data from POST
$firstName = $_POST['first_name'];
$lastName = $_POST['last_name'];
$addressLine1 = $_POST['address_line_1'];
$addressLine2 = $_POST['address_line_2'];
$windowData = $_POST['window_data'];
$windowDescriptions = $_POST['window_descriptions']; 
$tableData = $_POST['table_data']; 
$windowResults = $_POST['window_results'];

$defaultNotes = '• Colour Variation: There will be a colour variation between your new retro-fitted aluminium and the older existing aluminium. Modglass retrofit will remove a single bead on the second measure to be colour matched by local experts.<br>
• Hardware: Colour matched hardware can be ordered if required at extra cost.<br>
• Glass Supplied: All glass installed by Modglass retro-fit is Manufactured by local Metro Glass or Stake Glass.<br>
• Glazing Type: <br>
• Timber: Doors & windows if quoted have pre-primed timber beads; Modglass staff to explain the process.<br>
• Seals: New backing rubbers around fixed panels, opening windows (Sash), or slider door panels.<br>
• Waste: Single glazed glass and metal will be removed by Modglass retro-fit from homeowners\' property.<br>
• Glass Cleaning: Inside out window cleaners will make contact with the homeowner once Modglass receives final payment.<br>
• Ownership: All double glaze windows and metal are the property of Modglass retro-fit until final payment.<br>
• Warranties: Double glazed Warranty 10 years (All double glazing meets NZ4223.3.2016); Powdercoat Warranty 10 years; Workmanship Warranty 5 years.<br>
• Installation: Installation time frame will start from your 33% deposit. If the quote is accepted within 60 days from the quoted date, there will be a price guarantee.';

$additionalNotes = isset($_POST['additional_notes']) && !empty($_POST['additional_notes']) 
    ? nl2br($_POST['additional_notes']) 
    : $defaultNotes;

// Concatenate firstName and lastName with a space in between
$fullName = $firstName . ' ' . $lastName;

// Start of the HTML content:
$html = "<h2>Modglass Quote</h2>";
$html = "<h1>This is not a real quote</h1>";
$html .= "Quoted on " . date("d-m-Y") . "<br>";
$html .= "for {$fullName}<br>";
$html .= "{$addressLine1}<br>";
$html .= "{$addressLine2}<br>";
$html .= "<br>";
$html .= "<br>";
$html .= "Dear {$firstName},<br>";
$html .= "<br>";
$html .= "Modglass thanks you for the opportunity to provide you with your retrofit double glazing quotation. This quotation is broken down by room and double glazing solutions to allow you to choose the options that best suit your needs.<br>";
$html .= "<h4>Why choose Modglass for your retrofit double glazing?</h4><br>";
$html .= "Modglass is a leading provider of energy-efficient glass solutions, with a wide network of factories, retail outlets, and installation teams. Our double glazed units are manufactured to the highest quality standards and are subject to regular and independent testing.<br>";
$html .= "<h4>The key benefits of our double glazed units are:</h4><br>";
$html .= "<br>";
$html .= "• <strong>Comfort:</strong> Helps maintain a consistent temperature year-round.<br>";
$html .= "• <strong>Reduced energy bills:</strong> Less temperature variations equate to less heating or cooling, reducing your annual energy bills.<br>";
$html .= "• <strong>Reduced condensation:</strong> Promotes healthy living conditions.<br>";
$html .= "• <strong>Reduced noise:</strong> Double glazing can reduce noise transmission.<br>";
$html .= "• <strong>Safety:</strong> We ensure safety glass is used in all human impacted areas as required by relevant standards.<br>";
$html .= "<h4>Our Premium Glass Options:</h4><br>";
$html .= "<br>";
$html .= "• <strong>Max:</strong> Extra clear soft coat Low E glass, excellent for maximizing natural light without compromising thermal performance.<br>";
$html .= "• <strong>Xcel:</strong> High-performance soft coat Low E glass, ideal for top-tier thermal and solar control properties.<br>";
$html .= "• <strong>SunX Grey:</strong> Grey tone soft coat Low E glass, a stylish aesthetic choice for enhancing both performance and appearance.<br>";
$html .= "<h4>Our Standard Glass Option:</h4><br>";
$html .= "<br>";
$html .= "• <strong>Classic:</strong> A cost-effective solution that improves thermal insulation and reduces noise. Ideal for those looking for an affordable upgrade from standard single glazing.<br>";

// Iterate over windowData and format the output:
foreach($windowData as $windowId => $window) {
    $html .= "<h3>Window: {$windowId}</h3>";
    $html .= "<p>Description: {$window['description']}</p>";  // Use 'description' instead of 'window_description'
    
    // Calculate totals for each window
    $totalSqm = 0;
    $totalClassicPrice = 0;
    $totalMaxPrice = 0;
    $totalXcelPrice = 0;
    foreach($window['panes'] as $pane) {
        $totalSqm += $pane['sqm'];
        $totalClassicPrice += $pane['classic'];  // Assuming 'classic', 'max', and 'xcel' are properties of each pane
        $totalMaxPrice += $pane['max'];
        $totalXcelPrice += $pane['xcel'];
    }

    $html .= "<p>Total SQM: {$totalSqm}</p>";
    $html .= "<p>Total Classic Price: {$totalClassicPrice}</p>";
    $html .= "<p>Total Max Price: {$totalMaxPrice}</p>";
    $html .= "<p>Total Xcel Price: {$totalXcelPrice}</p>";
}

// Add table data to HTML content
if (!empty($windowResults)) {
    $html .= "<h3>Window Price Breakdown:</h3>";
    $html .= '<table style="border-collapse: collapse; width: 100%;">';  // Add border-collapse for continuous borders
    $html .= "<tr>";
    $html .= '<th style="border: 1px solid black; padding: 5px;"><strong>Window Description</strong></th>';  // Add border and padding to each cell
    $html .= '<th style="border: 1px solid black; padding: 5px;"><strong>Classic Price</strong></th>';
    $html .= '<th style="border: 1px solid black; padding: 5px;"><strong>Max Price</strong></th>';
    $html .= '<th style="border: 1px solid black; padding: 5px;"><strong>Xcel Price</strong></th>';
    $html .= '<th style="border: 1px solid black; padding: 5px;"><strong>SunX Grey Price</strong></th>'; // Added SunX Grey column header
    $html .= "</tr>";
    foreach ($windowResults as $windowResult) {
        $classic = $windowResult['window_total']['classic'] +
                    $windowResult['window_total']['materials'] +
                    $windowResult['window_total']['labour'] +
                    $windowResult['window_total']['handles'] +
                    $windowResult['window_total']['stay'] +
                    $windowResult['window_total']['wheels'];

        $max = $windowResult['window_total']['max'] +
                $windowResult['window_total']['materials'] +
                $windowResult['window_total']['labour'] +
                $windowResult['window_total']['handles'] +
                $windowResult['window_total']['stay'] +
                $windowResult['window_total']['wheels'];

        $xcel = $windowResult['window_total']['xcel'] +
                $windowResult['window_total']['materials'] +
                $windowResult['window_total']['labour'] +
                $windowResult['window_total']['handles'] +
                $windowResult['window_total']['stay'] +
                $windowResult['window_total']['wheels'];

        $sunxGrey = $windowResult['window_total']['sunxgrey'] +  // Assuming 'sunx_grey' is the key for SunX Grey price
                    $windowResult['window_total']['materials'] +
                    $windowResult['window_total']['labour'] +
                    $windowResult['window_total']['handles'] +
                    $windowResult['window_total']['stay'] +
                    $windowResult['window_total']['wheels'];

        $html .= "<tr>";
        $html .= '<td style="border: 1px solid black; padding: 5px;">' . $windowResult['window_description'] . '</td>';  // Add border and padding to each cell
        $html .= '<td style="border: 1px solid black; padding: 5px;">$' . number_format($classic, 2) . '</td>';
        $html .= '<td style="border: 1px solid black; padding: 5px;">$' . number_format($max, 2) . '</td>';
        $html .= '<td style="border: 1px solid black; padding: 5px;">$' . number_format($xcel, 2) . '</td>';
        $html .= '<td style="border: 1px solid black; padding: 5px;">$' . number_format($sunxGrey, 2) . '</td>'; // Added SunX Grey price cell
        $html .= "</tr>";
    }
    $html .= "</table>";
	$html .= "<br>";
	$html .= "<br>";
	$html .= "Upon acceptance, a 33% deposit would be required to source material for your home.
<br>";
	$html .= "<br>";
	$html .= "Should you wish to accept this quote, please contact us at your earliest convenience.";
	$html .= "<br>";
	$html .= "<br>";
	$html .= "Regards,";
	$html .= "<br>";
	$html .= "<br>";
	$html .= "Daniel Sutherland";
	$html .= "<br>";
	$html .= "<br>";
	$html .= "<br>";
}

// Add additional notes to HTML content
if (!empty($additionalNotes)) {
    $html .= "<h3>Additional Notes:</h3>";
    $html .= "<p>" . $additionalNotes . "</p>";
}
?>
