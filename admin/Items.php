<?php
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);
	require_once("../config.php");

	if(!isset($_GET['included'])){
		echo "Not allowed";
		die;
	}
?>
<link rel="stylesheet" type="text/css" href="../assets/css/admin/admin.css">
<link rel="stylesheet" type="text/css" href="../assets/css/admin/items.css">

<script type="text/javascript" src="../assets/js/jquery-3.2.1.min.js"></script>
<script type="text/javascript" src="../assets/js/admin/Items.js"></script>

	
<?php //php inits
	include($ini_array['BasePath']."Scripts/SQL.php");
	$SQL = new SQL($ini_array);
	$BaseURL = (isset($_SERVER['HTTPS']) ? "https://" : "http://").$ini_array['BaseURL'];
	echo "<script>
		var BaseURL = '$BaseURL';
	</script>";
?>
	
<?php
	$Items = $SQL->getItems();
	$Categories = $SQL->getCategories();
	if(count($Items)==0){
		echo "<style>#AddItemDiv{display:none;}</style>";
		echo "<div class='empty_view'>";
			echo "Create the first Item";
			echo "<button class='Button' onclick='createFirst();'>Create</button>";
		echo "</div>";
	}
	foreach($Items as $ItemItm){
		$ItemID = "Item_$ItemItm[id]";
		foreach($Categories as $CategoryItm){
			$CategoryID = $CategoryItm['id'];
			if($CategoryID === $ItemItm['category_id']){
				$Category = $CategoryItm;
			}
		}
		echo "<div class='list_element' id='$ItemID'>";
			echo "<div class='unselectable header'>";
				echo "$ItemItm[name]";
			echo "</div>";
			echo "<div class='content'>";
				echo "<form class='userForm' id='$ItemID' method='POST'>";
					echo "<input type='hidden' name='id' value='$ItemItm[id]'>";
					echo "<table class='ItemForm'>";
						echo "<tr>";
							echo "<td class='unselectable'>Name</td>";
							echo "<td style='padding:0px;'><input type='text' name='name' value='$ItemItm[name]'></td>";
						echo "</tr>";
						echo "<tr>";
							echo "<td class='unselectable'>Image</td>";
							echo "<td style='padding:0px;'><input type='file' name='icon_vector' id='icon_vector'></td>";
						echo "</tr>";
							$CategoryFields = array();
							echo "<tr>";
								echo "<td class='unselectable'>Category</td>";
								echo "<td>";
									echo "<select name='category' class='category_chooser'>";
										echo "<option value='empty' selected disabled>Empty</option>"; 
										foreach($Categories as $CategoryItm){
											$CategoryID = $CategoryItm['id'];
											$CategoryFields[$CategoryID] = json_decode($CategoryItm['data'], true);
											if($CategoryID === $ItemItm['category_id']){
												$chosen = "selected";
												$Category = $CategoryItm;
											} else {
												$chosen = "";
											}
											echo "<option value='$CategoryID' $chosen>$CategoryItm[name]</option>"; 
										}
									echo "</select>";
								echo "</td>";
							echo "</tr>";
							echo "<script>var CategoryFields = ".json_encode($CategoryFields, true).";</script>";
						echo "<tr>";
							echo "<td class='unselectable'>Fields</td>";
							echo "<td style='padding:0px;'>";
								echo "<table class='FieldTable'>";
									echo "<tr>";
										echo "<th>Name</th>";
										echo "<th>Type</th>";
										echo "<th>Default</th>";
									echo "</tr>";
									$Fields = json_decode($Category["data"], true);
									$FieldsValues = json_decode($ItemItm["data"], true);
									for($i = 0; $i< count($Fields); $i++){
										$Itm = $Fields[$i];
										$Name = $Itm['field'];
										$Type = $Itm['type'];
										$ID = $Itm['id'];
										$FieldValue = $FieldsValues[$ID];
										
										echo "<tr class='category_content'>";
											echo "<td>";
												echo $Name;
											echo "</td>";
											echo "<td>";
												$options = array(
													"calculation" => "Calculation",
													"smalltext" => "Small Text",
													"largetext" => "Large Text",
													"image" => "Image");
												echo $options[$Type];
											echo "</td>";
											echo "<td>";
												echo "<input type='text' name='$ID' value='$FieldValue'>";
											echo "</td>";
										echo "</tr>";
									}
								echo "</table>";
							echo "</td>";
						echo "</tr>";
						echo "<tr>";
							echo "<td style='padding-right:0px;' align='right' colspan='2'>";
								echo "<button class='removeButton type='submit' formaction='".$BaseURL."Scripts/Item.php?action=Remove'>Remove</button>";
								echo "<button class='saveButton type='submit' formaction='".$BaseURL."Scripts/Item.php?action=Update'>Save</button>";
							echo "</td>";
						echo "</tr>";
					echo "</table>";
				echo "</form>";
			echo "</div>";
		echo "</div>";
	}
?>

<div class='list_element' id='AddItemDiv'>
	<div class='unselectable header'>
		Add Item
	</div>
	<div class='content'>
		<form class='AddItemForm' id='AddItem' method='POST'>
			<table class="ItemForm">
				<tr>
					<td class='unselectable'>Name</td>
					<td style='padding:0px;'><input type='text' name='name'></td>
				</tr>
				<tr>
					<td class='unselectable'>Image</td>
					<td style='padding:0px;'><input type="file" name="icon_vector" id="icon_vector"></td>
				</tr>
				<?php
					$Categories = $SQL->getCategories();
					$CategoryFields = array();
					echo "<tr>";
						echo "<td class='unselectable'>Category</td>";
						echo "<td>";
							echo "<select name='category' class='category_chooser'>";
								echo "<option value='empty' selected disabled>Empty</option>"; 
								foreach($Categories as $CategoryItm){
									$CategoryID = $CategoryItm['id'];
									$CategoryFields[$CategoryID] = json_decode($CategoryItm['data'], true);
									echo "<option value='$CategoryID'>$CategoryItm[name]</option>"; 
								}
							echo "</select>";
						echo "</td>";
					echo "</tr>";
					echo "<script>var CategoryFields = ".json_encode($CategoryFields, true).";</script>";
				?>
				<tr>
					<td class='unselectable'>Fields</td>
					<td style='padding:0px;'>
						<table class='FieldTable'>
							<tr>
								<th>Name</th>
								<th>Type</th>
								<th>Default</th>
							</tr>
						</table>
					</td>
				</tr>
				<tr class='saveRow'>
					<td style='padding-right:0px;' align='right' colspan='2'>
						<button class='saveButton' onClick='addItem()'>Save</button>
					</td>
				</tr>
			</table>
		</form>
	</div>
</div>
