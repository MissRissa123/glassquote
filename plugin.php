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

        return array(
            'sqm' => $sqm,
            'classic' => $sqm * $glassPrices[$glassType]['classic'],
            'max' => $sqm * $glassPrices[$glassType]['max'],
            'xcel' => $sqm * $glassPrices[$glassType]['xcel'],
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
		$windowData = $_POST['window_data'];
		$windowDescriptions = $_POST['window_descriptions'];
		$results = array();

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
    $windowData = $_POST['window_data'];
    $windowDescriptions = $_POST['window_descriptions']; // Fetch window descriptions from POST
    $tableData = $_POST['table_data']; // Fetch table data from POST
	
	

    // Start of the HTML content:
    $html = "<h2>Window Size Calculation Results</h2>";

    // Iterate over windowData and format the output:
    foreach($windowData as $windowId => $window) {
        $html .= "<h3>Window: {$windowId}</h3>";
        $html .= "<p>Description: {$window['window_description']}</p>";
        $html .= "<p>Total SQM: {$window['window_total']['sqm']}</p>";
        $html .= "<p>Total Classic Price: {$window['window_total']['classic']}</p>";
        $html .= "<p>Total Max Price: {$window['window_total']['max']}</p>";
        $html .= "<p>Total Xcel Price: {$window['window_total']['xcel']}</p>";
    }

    // Add table data to HTML content
    if(!empty($tableData)) {
        $html .= "<h3>Table Data:</h3>";
        $html .= "<table>";
        foreach($tableData as $row) {
            $html .= "<tr>";
            foreach($row as $cell) {
                $html .= "<td>{$cell}</td>";
            }
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
