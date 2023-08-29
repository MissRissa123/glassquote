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
		add_action('wp_ajax_generate_spec_sheet_pdf', array($this, 'generate_spec_sheet_pdf'));
		add_action('wp_ajax_nopriv_generate_spec_sheet_pdf', array($this, 'generate_spec_sheet_pdf'));
    }
	

    public function window_calculator_form()
{
    ob_start();
    ?>
    <style>
        #buttonContainer {
            position: fixed;
            bottom: 10px;
            left: 10px;
            z-index: 1000;
            display: flex;
            gap: 10px; /* Space between the buttons */
        }
    </style>
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
				<th>Total SunX Grey Price</th>
            </tr>
        </thead>
        <tbody>
            <!-- Summary data will go here -->
        </tbody>
    </table>
	<label for="additional_notes">Additional Notes:</label><br>
	<textarea name="additional_notes" id="additional_notes" rows="20" cols="50" style="width: 100%; max-width: 100%;">
• Colour Variation: There will be a colour variation between your new retro-fitted aluminium and the older existing aluminium. Modglass retrofit will remove a single bead on the second measure to be colour matched by local experts.
• Hardware: Colour matched hardware can be ordered if required at extra cost.
• Glass Supplied: All glass installed by Modglass retro-fit is Manufactured by local Metro Glass or Stake Glass.
• Glazing Type: 
• Timber: Doors & windows if quoted have pre-primed timber beads; Modglass staff to explain the process.
• Seals: New backing rubbers around fixed panels, opening windows (Sash), or slider door panels.
• Waste: Single glazed glass and metal will be removed by Modglass retro-fit from homeowners' property.
• Glass Cleaning: Inside out window cleaners will make contact with the homeowner once Modglass receives final payment.
• Ownership: All double glaze windows and metal are the property of Modglass retro-fit until final payment.
• Warranties: Double glazed Warranty 10 years (All double glazing meets NZ4223.3.2016); Powdercoat Warranty 10 years; Workmanship Warranty 5 years.
• Installation: Installation time frame will start from your 33% deposit. If the quote is accepted within 60 days from the quoted date, there will be a price guarantee.
</textarea>
    <div id="buttonContainer">
        <button id="calculate" type="button">Calculate</button>
        <button id="generatePdf">Generate PDF</button>
		<button id="generateSpecSheetPdf">Download Spec Sheet</button>

    </div>
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
			'xcel' => $sqm * $glassPrices[$glassType]['xcel'] * $markupXcel,
			'sunxgrey' => $sqm * $glassPrices[$glassType]['sunxgrey'] * $markupSunxgrey,
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
			'sunxgrey' => 0,
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
			$windowTotalResults['sunxgrey'] += $paneResult['sunxgrey']; 
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

    // Include the HTML content from the separate file
    include 'pdf_template.php';

    // Print text using writeHTMLCell()
    $pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);

    // Close and output PDF document
    $output = $pdf->Output('Window_Calculation_Results.pdf', 'I'); // This will output the PDF to the browser
    echo $output;
    die();
}
public function generate_spec_sheet_pdf() {
    // create new PDF document
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    // set document information
    $pdf->SetAuthor('Dan Sutherland');
    $pdf->SetTitle('Quote Spec Sheet');
    $pdf->SetSubject('Spec Sheet');
    $pdf->SetKeywords('TCPDF, PDF, spec sheet');

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

    // Include the HTML content from the separate file
    include 'pdf_spec_sheet.php';

    // Print text using writeHTMLCell()
    $pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);

    // Close and output PDF document
    $output = $pdf->Output('Quote_Spec_Sheet.pdf', 'I'); // This will output the PDF to the browser
    echo $output;
    die();
}



}

new WindowSizeCalculator();
