jQuery(document).ready(function($) {
    let windowCount = 1;
    let paneCounts = {1: 1};
	let windowResultsArray = [];

    $('#addWindow').click(function() {
        if (windowCount >= 40) {
            alert('Maximum number of windows is 40');
            return;
        }
        windowCount++;
        paneCounts[windowCount] = 1;
		let newWindow = $('.window:first').clone().attr('id', 'window' + windowCount);
		let newWindowDescription = newWindow.find('#windowDescription1').attr('id', 'windowDescription' + windowCount);
		newWindowDescription.val(''); // Reset the window description field

        newWindow.find('h2').text('Window ' + windowCount);
        newWindow.find('.pane:not(:first)').remove();
        let newPane = newWindow.find('.pane:first').attr('id', 'pane' + windowCount + '1');
        newPane.find('h2').text('Pane 1');
        newPane.append('<button class="addPane" type="button">Add Pane</button>');
        newWindow.appendTo('#window-calculator');

        // Reset the form fields in the new window
        newPane.find('input').val('');
        newPane.find('select').prop('selectedIndex',0); // Reset select box
        newPane.find('.pane-result').html(''); // Clear the result fields
        newPane.find('.wheelsContainer').hide(); // Hide wheels by default
    });

    $(document).on('click', '.addPane', function() {
        let parentWindow = $(this).closest('.window');
        let parentWindowId = parseInt(parentWindow.attr('id').replace('window', ''));
        if (paneCounts[parentWindowId] >= 12) {
            alert('Maximum number of panes for each window is 12');
            return;
        }
        paneCounts[parentWindowId]++;
        let newPane = $('.pane:first').clone().attr('id', 'pane' + parentWindowId + paneCounts[parentWindowId]);
        newPane.find('h2').text('Pane ' + paneCounts[parentWindowId]);
        $(this).remove();
        newPane.append('<button class="addPane" type="button">Add Pane</button>');
        newPane.appendTo(parentWindow);

        // Reset the form fields in the new pane
        newPane.find('input').val('');
        newPane.find('select').prop('selectedIndex',0); // Reset select box
        newPane.find('.pane-result').html(''); // Clear the result fields
        newPane.find('.wheelsContainer').hide(); // Hide wheels by default
    });

    $(document).on('change', '.paneType', function() {
        let paneType = $(this).val();
        let $wheelsContainer = $(this).closest('.pane').find('.wheelsContainer');
        let $wheelsSelect = $wheelsContainer.find('.wheels');

        if (["Sliding Door", "Sliding Door Sash", "Sliding Door Fixed", "Stacker Door", "Stacker Door Sash", "Stacker Door Fixed"].includes(paneType)) {
            $wheelsContainer.show();
        } else {
            $wheelsSelect.val('no'); // Reset wheels to "no"
            $wheelsContainer.hide();
        }
    });


	function getPaneData(pane) {
			let paneId = pane.attr('id');
			let width = $('#' + paneId + ' .width').val();
			let height = $('#' + paneId + ' .height').val();
			let paneType = $('#' + paneId + ' .paneType').val();
			let glassType = $('#' + paneId + ' .glassType').val();
			let wheels = $('#' + paneId + ' .wheels').val();
			let handles = $('#' + paneId + ' .handles').val();
			let sqm = width / 1000 * height / 1000;
			return {width: width, height: height, paneType: paneType, glassType: glassType, wheels: wheels, handles: handles, sqm: sqm};
		}

		function getWindowData(window) {
			let windowId = window.attr('id');
			let windowDescription = window.find('#windowDescription' + windowId.replace('window', '')).val(); // Get window description
			let paneData = [];
			let windowSqmTotal = 0;
			window.find('.pane').each(function() {
				let pane = getPaneData($(this));
				windowSqmTotal += pane.sqm;
				paneData.push(pane);
			});
			$('#' + windowId + ' .total-sqm').html('Total SQM: ' + windowSqmTotal.toFixed(2));
			return {description: windowDescription, panes: paneData}; // Include window description here
		}


		function updatePaneResults(pane, paneResult) {
			pane.find('.pane-result').empty().append(
				'<tr>'+
				'<td>'+paneResult.sqm.toFixed(2)+'</td>'+
				'<td>$'+paneResult.classic.toFixed(2)+'</td>'+
				'<td>$'+paneResult.max.toFixed(2)+'</td>'+
				'<td>$'+paneResult.xcel.toFixed(2)+'</td>'+
				'<td>$'+paneResult.stay.toFixed(2)+'</td>'+
				'<td>$'+paneResult.wheels.toFixed(2)+'</td>'+
				'<td>$'+paneResult.handles.toFixed(2)+'</td>'+
				'<td>$'+(paneResult.sqm * 85).toFixed(2)+'</td>'+
				'<td>$'+paneResult.labour.toFixed(2)+'</td>'+
				'</tr>'
			);
		}
		function updateWindowTotals(windowId, windowTotal) {
			if (windowTotal) {
				$('#' + windowId + ' .total-sqm').html(windowTotal.sqm.toFixed(2));
				$('#' + windowId + ' .total-classic').html('$' + windowTotal.classic.toFixed(2));
				$('#' + windowId + ' .total-max').html('$' + windowTotal.max.toFixed(2));
				$('#' + windowId + ' .total-xcel').html('$' + windowTotal.xcel.toFixed(2));
				$('#' + windowId + ' .total-stay').html('$' + windowTotal.stay.toFixed(2));
				$('#' + windowId + ' .total-wheels').html('$' + windowTotal.wheels.toFixed(2));
				$('#' + windowId + ' .total-handles').html('$' + windowTotal.handles.toFixed(2));
				$('#' + windowId + ' .total-materials').html('$' + windowTotal.materials.toFixed(2));
				$('#' + windowId + ' .total-labour').html('$' + windowTotal.labour.toFixed(2));
			} else {
				console.log('windowTotal is undefined for window', windowId);
			}
		}				
		function createResultsTableWindow() {
			return `
				<table id="resultsTableWindow">
					<thead>
						<tr>
							<th>Total SQM</th>
							<th>Total Classic Price</th>
							<th>Total Max Price</th>
							<th>Total Xcel Price</th>
							<th>Total Stay Price</th>
							<th>Total Wheels</th>
							<th>Total Handles</th>
							<th>Total Materials</th>
							<th>Total Labour</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td class="total-sqm"></td>
							<td class="total-classic"></td>
							<td class="total-max"></td>
							<td class="total-xcel"></td>
							<td class="total-stay"></td>
							<td class="total-wheels"></td>
							<td class="total-handles"></td>
							<td class="total-materials"></td>
							<td class="total-labour"></td>
						</tr>
					</tbody>
				</table>
			`;
		}

		$('#calculate').click(function() {
			windowResultsArray = []; // Clear previous results
			let windowData = {};
			let windowDescriptions = {}; // Create an object to store window descriptions
			$('.window').each(function() {
				let windowId = $(this).attr('id');
				let windowDataObject = getWindowData($(this)); // Changed variable name to avoid conflict
				windowData[windowId] = windowDataObject.panes;
				windowDescriptions[windowId] = windowDataObject.description; // Store window description
			});
			$.ajax({
				url: window_calculator_vars.ajax_url,
				type: 'post',
				data: {
					action: 'calculate_window',
					window_data: windowData,
					window_descriptions: windowDescriptions // Send window descriptions to the server
				},
				success: function(result) {
					let results = result.data;

					// Clear the summary table before adding new data
					$('#summaryTable tbody').empty();
					for (let windowId in results) {
						if (results.hasOwnProperty(windowId)) {
							let windowResults = results[windowId];
							let paneCount = $('#' + windowId + ' .pane').length;
							$('#' + windowId + ' .pane').each(function(index) {
								if(windowResults && windowResults.pane_results && index < windowResults.pane_results.length){
									let paneResult = windowResults.pane_results[index];
									updatePaneResults($(this), paneResult);
								}
							});
							if(windowResults && windowResults.window_total) {
								// Remove the existing results table if it exists
								$('#' + windowId + ' #resultsTableWindow').remove();
								// Append the new results table
								$('#' + windowId).append(createResultsTableWindow());
								// Update the values in the new table
								updateWindowTotals(windowId, windowResults.window_total);
							}

							// Add a row to the summary table for each window
							$('#summaryTable tbody').append(`
								<tr>
								<td>${windowResults.window_description}</td>
								<td>${windowResults.window_total.sqm.toFixed(2)}</td>
								<td>$${(windowResults.window_total.classic + windowResults.window_total.materials + windowResults.window_total.labour + windowResults.window_total.handles + windowResults.window_total.stay + windowResults.window_total.wheels).toFixed(2)}</td>
								<td>$${(windowResults.window_total.max + windowResults.window_total.materials + windowResults.window_total.labour + windowResults.window_total.handles + windowResults.window_total.stay + windowResults.window_total.wheels).toFixed(2)}</td>
								<td>$${(windowResults.window_total.xcel + windowResults.window_total.materials + windowResults.window_total.labour + windowResults.window_total.handles + windowResults.window_total.stay + windowResults.window_total.wheels).toFixed(2)}</td>
								</tr>
							`);

							// Store the windowResults to the array
							windowResultsArray.push(windowResults);
						}
					}
				},
				error: function(jqXHR, textStatus, errorThrown) {
					console.log(textStatus, errorThrown);
				}
			});
			$('#summaryTable').after('<button id="generatePdf">Generate PDF</button>');
		});
		// Function to get the results data for pdf
		function getResultsDataForPdf() {
			return windowResultsArray;
		}
		
		function generatePDF() {
    let windowResultsData = getResultsDataForPdf(); // Call the function to get the results data
    let windowData = {};
    let windowDescriptions = {};
    let tableData = [];

    $('.window').each(function() {
        let windowId = $(this).attr('id');
        let tableDataForWindow = getTableDataForWindow(windowId);
        tableData = tableData.concat(tableDataForWindow);
    });

    $.ajax({
        url: window_calculator_vars.ajax_url,
        type: 'post',
        data: {
            action: 'generate_pdf',
            window_data: windowData,
            window_descriptions: windowDescriptions,
            table_data: tableData,
            window_results: windowResultsData // Add this line to include the windowResultsData in the AJAX request
        },
		
        xhrFields: {
            responseType: 'blob'
        },
        success: function(response, status, xhr) {
            var blob = new Blob([response], {type: xhr.getResponseHeader('Content-Type')});
            var link = document.createElement('a');
            link.href = window.URL.createObjectURL(blob);
            link.download = "window_calculation.pdf";
            link.click();
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.log(textStatus, errorThrown);
        }
    });
}

		function getTableDataForWindow(windowId) {
			let tableDataForWindow = [];
			let windowElement = $('#' + windowId);
			let windowData = getWindowData(windowElement);
			for (let pane of windowData.panes) {
				let rowData = [
					pane.width,
					pane.height,
					pane.paneType,
					pane.glassType,
					pane.wheels,
					pane.handles,
					pane.sqm.toString()
				];
				tableDataForWindow.push(rowData);
			}
			return tableDataForWindow;
		}


		$('#generatePdf').click(function() {
			generatePDF();
		});

});
