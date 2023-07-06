<style>
    .window,
    .pane {
        margin-bottom: 25px; 
    }
    .pane {
        margin-top: 25px;
    }
    #summaryTable,
    #resultsTable,
    #resultsTableWindow {
        border-collapse: collapse;
        margin-top: 25px; 
		margin-bottom: 25px;
    }
    #summaryTable th, #summaryTable td,
    #resultsTable th, #resultsTable td,
    #resultsTableWindow th, #resultsTableWindow td {
        text-align: left;
        border: 1px solid;
        width: 200px;
    }
</style>
<div id="pane11" class="pane">
    <h2>Pane 1</h2>
    <label for="width11">Width (in millimeters): </label>
    <input type="number" class="width" id="width11" name="width11" step="1" min="0"><br>
    <label for="height11">Height (in millimeters): </label>
    <input type="number" class="height" id="height11" name="height11" step="1" min="0"><br>
    <label for="paneType11">Pane Type: </label>
    <select class="paneType" id="paneType11" name="paneType11">
        <option value="Fixed Pane">Fixed Pane</option>
        <option value="Awning Sash">Awning Sash</option>
        <option value="Casement Sash">Casement Sash</option>
        <option value="Hinged Door">Hinged Door</option>
        <option value="Bifold Door">Bifold Door</option>
        <option value="Sliding Door">Sliding Door</option>
        <option value="Sliding Door Sash">Sliding Door Sash</option>
        <option value="Sliding Door Fixed">Sliding Door Fixed</option>
        <option value="Stacker Door">Stacker Door</option>
        <option value="Stacker Door Sash">Stacker Door Sash</option>
        <option value="Stacker Door Fixed">Stacker Door Fixed</option>
    </select><br>
    <label for="glassType11">Glass Type: </label>
    <select class="glassType" id="glassType11" name="glassType11">
        <option value="4cl//4cl">4cl//4cl</option>
        <option value="5cl//5cl">5cl//5cl</option>
    </select><br>
    <div class="wheelsContainer" style="display:none;">
        <label for="wheels11">Wheels: </label>
        <select class="wheels" id="wheels11" name="wheels11">
            <option value="no">No</option>
            <option value="yes">Yes</option>
        </select>
    </div>
	<div class="handlesContainer">
		<label for="handles11">Handles: </label>
		<input type="number" class="handles" id="handles11" name="handles11" min="0" step="1" value="0"><br>
	</div>
    <div></div>
	<table id="resultsTable">
    <thead>
        <tr>
            <th>SQM</th>
            <th>Classic Price</th>
            <th>Max Price</th>
            <th>Xcel Price</th>
            <th>Stay Price</th>
            <th>Wheels</th>
            <th>Handles</th>
            <th>Materials</th>
            <th>Labour</th>
        </tr>
    </thead>
    <tbody class="pane-result">
        <!-- Your table data should go here. 
        Each row should have a <tr> tag, and each piece of data should have a <td> tag.
        -->
    </tbody>
</table>
<button id="generatePdf">Generate PDF</button>
</div>
