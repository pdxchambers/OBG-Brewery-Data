<?php 
	/*Creates the actual admin panel for the plugin.*/
global $wpdb;
?>
<div class="wrap">
	<h2>OBG Brewery Data Options</h2>
	<hr>
	<h3>Single Record Operations</h3>
	<h4>Insert, Delete, Update</h4>
	<p>Enter SQL operation command in the field below and press the "Execute" button. The command must be valid SQL Syntax.</p>
	<form style="margin-bottom: 5px;" action=" <?php echo esc_url( admin_url( 'admin_post.php' ) ); ?>" method="post">
		<fieldset>
			<table>
				<tr>
					<td colspan="2">			
						<label for="brewery_name">Brewery Name:</label><br>
						<input type="text" id="brewery_name" name="brewery_name">
					</td>
					<td colspan="2">
						<label for="brewery_phone">Phone Number:</label><br>
						<input type="text" id="brewery_phone" name="brewery_phone">
					</td>
				</tr>
				<tr>
					<td>
						<label for="brewery_address">Address:</label><br>
						<input type="text" id="brewery_address" name="brewery_address">
					</td>
					<td>
						<label for="brewery_city">City:</label><br>
						<input type="text" id="brewery_city" name="brewery_city">
					</td>
					<td>
						<label for="brewery_state">State:</label><br>
						<input type="text" id="brewery_state" name="brewery_state">
					</td>
					<td>
						<label for="brewery_zip">Zip:</label><br>
						<input type="text" id="brewery_zip" name="brewery_zip">
					</td>
				</tr>
				<tr>
					<td>
						<label for="brewery_website">Website:</label><br>
						<input type="text" id="brewery_website" name="brewery_website">
					</td>
					<td>
						<label for="brewery_twitter">Twitter:</label><br>
						<input type="text" id="brewery_twitter" name="brewery_twitter">
					</td>
					<td>
						<label for="brewery_instagram">Instagram:</label><br>
						<input type="text" id="brewery_instagram" name="brewery_instagram">
					</td>
					<td>
						<label for="brewery_facebook">Faceboook:</label><br>
						<input type="text" id="brewery_facebook" name="brewery_facebook">
					</td>
				</tr>
				<tr>
					<td colspan="4">
						<label for="brewery_notes">Notes:</label><br>
						<input type="text" id="brewery_notes" name="brewery_notes">
					</td>
				</tr>
				<tr>
					<td colspan="2">
						<label for="query_type">Query Type:</label><br>
						<select id="query_type" name="query_type">
							<option value="update">Update</option>
							<option value="insert">Insert</option>
							<option value="delete">Delete</option>
						</select>
					</td>
					<td colspan="2">
						<input type="hidden" name="action" value="admin_update_panel">
						<input type="submit" value="Execute Query">
					</td>
				</tr>
			</table>
		</fieldset>
	</form>
</div>