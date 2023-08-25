<?php
/*
Plugin Name: Window Size Calculator
Description: A plugin to calculate window square meters and cost based on window grades.
Version: 1.0
Author: Your Name
Author URI: Your Website
*/
require_once('tcpdf/tcpdf.php'); // Add this line to the top of your plugin file to include TCPDF library
class WindowSizeCalculator
{
    public function __construct()
    {
        $this->config = include 'config.php';
        add_shortcode('window_calculator', array($this, 'window_calculator_form'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_ajax_calculate_window', array($this, 'calculate_window'));
        add_action('wp_ajax_nopriv_calculate_window', array($this, 'calculate_window'));
        add_action('wp_ajax_generate_pdf', array($this, 'generate_pdf')); // Add this line
        add_action('wp_ajax_nopriv_generate_pdf', array($this, 'generate_pdf')); // And this line
    }
	

    public function window_calculator_form()
	{
		ob_start();
		?>
		<div>
		<label for="first_name">First Name:</label><br>
		<input type="text" id="first_name" name="first_name"><br>

		<label for="last_name">Last Name:</label><br>
		<input type="text" id="last_name" name="last_name"><br>

		<label for="address_line_1">Address Line 1:</label><br>
		<input type="text" id="address_line_1" name="address_line_1"><br>

		<label for="address_line_2">Address Line 2:</label><br>
		<input type="text" id="address_line_2" name="address_line_2"><br>
		</div>
		<div id="window-calculator">
			<div id="window1" class="window">
				<h2>Window 1</h2>
				<label for="windowDescription1">Window Description: </label>
				<input type="text" id="windowDescription1" name="windowDescription1"><br>
				<?php include 'pane_form.php'; ?>
				<button class="addPane" type="button">Add Pane</button>
			</div>
			<button id="addWindow" type="button">Add Window</button>
			<button id="calculate" type="button">Calculate</button>
		</div>
		<div id="totalResult"></div>
		<table id="summaryTable">
			<thead>
				<tr>
					<th>Window Description</th>
					<th>Total SQM</th>
					<th>Total Classic Price</th>
					<th>Total Max Price</th>
					<th>Total Xcel Price</th>
				</tr>
			</thead>
			<tbody>
				<!-- Summary data will go here -->
			</tbody>
		</table>
		<button id="generatePdf">Generate PDF</button>
		<?php
		return ob_get_clean();
	}


    public function enqueue_scripts()
    {
        wp_enqueue_script('window_calculator', plugins_url('window_calculator.js', __FILE__), array('jquery'), '1.0', true);
        wp_localize_script('window_calculator', 'window_calculator_vars', array(
            'ajax_url' => admin_url('admin-ajax.php'),
        ));
    }

private function calculate_pane($pane)
    {
        $glassPrices = $this->config['glassPrices'];
        $wheelsPrice = $this->config['wheelsPrice'];
        $stayPrices = $this->config['stayPrices'];
        $handlePrice = $this->config['handlePrice'];
        $materialPricePerSqm = $this->config['materialPricePerSqm'];
        $labourPricePerSqm = $this->config['labourPricePerSqm'];

        $width = $pane['width'] / 1000;
        $height = $pane['height'] / 1000;
        $glassType = $pane['glassType'];
        $wheels = $pane['wheels'];
        $paneType = $pane['paneType'];
        $sqm = $width * $height;
        $stay = isset($stayPrices[$paneType]) ? $stayPrices[$paneType] : 0;
        $wheelsCost = $wheels == 'yes' ? $wheelsPrice : 0;
        $handles = intval($pane['handles']);
        $handlesCost = $handles * $handlePrice;

	$markupClassic = 1.50; // 50% markup
    	$markupMax = 1.50;     // 50% markup
   	$markupXcel = 1.50;   // 50% markup
    	$markupSunxgrey = 1.65; // 65% markup

        return array(
            'sqm' => $sqm,
       	 'classic' => $sqm * $glassPrices[$glassType]['classic'] * $markupClassic,
        'max' => $sqm * $glassPrices[$glassType]['max'] * $markupMax,
        'xcel' => ($glassType == 'sunxgrey') ? $sqm * $glassPrices[$glassType]['xcel'] * $markupSunxgrey : $sqm * $glassPrices[$glassType]['xcel'] * $markupXcel,
            'stay' => $stay,
            'wheels' => $wheelsCost,
            'handles' => $handlesCost,
            'materials' => $sqm * $materialPricePerSqm,
            'labour' => $sqm * $labourPricePerSqm
        );
    }

    private function calculate_window_total($paneResults)
    {
        $windowTotalResults = array(
            'sqm' => 0,
            'classic' => 0,
            'max' => 0,
            'xcel' => 0,
            'stay' => 0,
            'wheels' => 0,
            'handles' => 0,
            'materials' => 0,
            'labour' => 0
        );

        foreach ($paneResults as $paneResult) {
            $windowTotalResults['sqm'] += $paneResult['sqm'];
            $windowTotalResults['classic'] += $paneResult['classic'];
            $windowTotalResults['max'] += $paneResult['max'];
            $windowTotalResults['xcel'] += $paneResult['xcel'];
            $windowTotalResults['stay'] += $paneResult['stay'];
            $windowTotalResults['wheels'] += $paneResult['wheels'];
            $windowTotalResults['handles'] += $paneResult['handles'];
            $windowTotalResults['materials'] += $paneResult['materials'];
            $windowTotalResults['labour'] += $paneResult['labour'];
        }

        return $windowTotalResults;
    }

	public function calculate_window()
		{
		// Fetch data from POST
		$windowData = $_POST['window_data'];
		$windowDescriptions = $_POST['window_descriptions']; // Fetch window descriptions from POST
		$tableData = $_POST['table_data']; // Fetch table data from POST
		$windowResultsArray = $_POST['window_results']; // Fetch windowResultsArray from POST
		error_log(print_r($tableData, true)); // Log the table data
		error_log(print_r($windowResultsArray, true)); // Log the windowResultsArray


		foreach ($windowData as $windowId => $paneData) {
			$paneResults = array();

			foreach ($paneData as $pane) {
				$paneResults[] = $this->calculate_pane($pane);
			}

			$results[$windowId] = array(
				'pane_results' => $paneResults, 
				'window_total' => $this->calculate_window_total($paneResults),
				'window_description' => $windowDescriptions[$windowId] // Add window description here
			);
		}

		wp_send_json_success($results);
	}
	public function generate_pdf()
{
    // create new PDF document
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    // set document information
    $pdf->SetAuthor('Dan Sutherland');
    $pdf->SetTitle('Window Size Calculation Results');
    $pdf->SetSubject('TCPDF Tutorial');
    $pdf->SetKeywords('TCPDF, PDF, example, test, guide');

    // set default header data
    $pdf->SetHeaderData('', 0, 'Modglass', 'https://modglass.co.nz');

    // set header and footer fonts
    $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

    // set default monospaced font
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

    // set margins
    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

    // set auto page breaks
    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

    // set image scale factor
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

    // add a page
    $pdf->AddPage();

    // Fetch data from POST
	$firstName = $_POST['first_name'];
	$lastName = $_POST['last_name'];
	$addressLine1 = $_POST['address_line_1'];
	$addressLine2 = $_POST['address_line_2'];
	$windowData = $_POST['window_data'];
	$windowDescriptions = $_POST['window_descriptions']; 
	$tableData = $_POST['table_data']; 
	$windowResults = $_POST['window_results']; // Fetch window results data from POST
	
	// Concatenate firstName and lastName with a space in between
	$fullName = $firstName . ' ' . $lastName;

	// Start of the HTML content:
	$html .= "<p>{$fullName}</p>";
	$html .= "<p>{$addressLine1}</p>";
	$html .= "<p>{$addressLine2}</p>";
	$html .= "<h2>Double Glazing Window Quote</h2>";
	$html .= "<p>Modglass Double Glazing thanks you for the opportunity to provide you with your Modglass double glazing quotation. This quotation is categorized by room and double glazing solutions, allowing you to select the options that best fit your needs.</p>";
	$html .= "<p>Our state-of-the-art production lines manufacture Metro's double glazed units, adhering to established quality standards. These units undergo regular and independent testing by BRANZ for durability based on EN1279.</p>";
	$html .= "<p>Our team of highly trained and skilled glaziers ensures a seamless and prompt installation process, resulting in a high-quality finished product. The key advantages of our Low E double glazed units include: Download our brochure by clicking this link.</p>";
	$html .= "<p>We are confident that you will enjoy the benefits of our double glazing. If you have any further questions, please feel free to contact us or visit our Modglass website to learn about the positive experiences of other satisfied customers. Please refer to the following page for your quote options. We eagerly await your favorable response. Upon acceptance, a 25% deposit will be required to procure materials for your home.</p>";

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
    $html .= "<h3>Window Results:</h3>";
    $html .= "<table>";
    $html .= "<tr>";
	$html .= "<th><strong>Window Description</strong></th>";
	$html .= "<th><strong>SQM</strong></th>";
	$html .= "<th><strong>Classic Price</strong></th>";
	$html .= "<th><strong>Max Price</strong></th>";
	$html .= "<th><strong>Xcel Price</strong></th>";
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

        $html .= "<tr>";
        $html .= "<td>{$windowResult['window_description']}</td>";
        $html .= "<td>" . number_format($windowResult['window_total']['sqm'], 2) . "</td>";
        $html .= "<td>$" . number_format($classic, 2) . "</td>";
        $html .= "<td>$" . number_format($max, 2) . "</td>";
        $html .= "<td>$" . number_format($xcel, 2) . "</td>";
        $html .= "</tr>";
    }
    $html .= "</table>";
}



    // Print text using writeHTMLCell()
    $pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);

    // Close and output PDF document
    $output = $pdf->Output('Window_Calculation_Results.pdf', 'I'); // This will output the PDF to the browser
    echo $output;
    die();
}

}

new WindowSizeCalculator();
