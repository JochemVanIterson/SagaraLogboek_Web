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
<link rel="stylesheet" type="text/css" href="../assets/css/admin/categories.css">

<script type="text/javascript" src="../assets/js/jquery-3.2.1.min.js"></script>
<script type="text/javascript" src="../assets/js/admin/Categories.js"></script>

	
<?php //php inits
	include($ini_array['BasePath']."Scripts/SQL.php");
	$SQL = new SQL($ini_array);
	$BaseURL = (isset($_SERVER['HTTPS']) ? "https://" : "http://").$ini_array['BaseURL'];
	echo "<script>
		var BaseURL = '$BaseURL';
	</script>";
?>
	
<?php
	$Categories = $SQL->getCategories(); // Get data from server
	if(count($Categories)==0){
		echo "<style>#AddCategoryDiv{display:none;}</style>";
		echo "<div class='empty_view'>";
			echo "Create the first Category";
			echo "<button class='Button' onclick='createFirst();'>Create</button>";
		echo "</div>";
	}
	$addFieldInc = 0; // New fields get an incrementing id
	foreach($Categories as $CategoryItm){
		$CategoryID = "Category_$CategoryItm[id]";
		echo "<div class='list_element' id='$CategoryID'>";
			echo "<div class='unselectable header'>";
				echo "$CategoryItm[name]";
			echo "</div>";
			echo "<div class='content'>";
				echo "<form class='userForm' enctype='multipart/form-data' id='$CategoryID' method='POST'>";
					echo "<table  class='CategoryForm'>";
						echo "<input type='hidden' name='id' value='$CategoryItm[id]'>";
						echo "<tr>";
							echo "<td class='unselectable'>Name</td>";
							echo "<td style='padding:0px;'><input type='text' name='name' value='$CategoryItm[name]'></td>";
						echo "</tr>";
						echo "<tr>";
							echo "<td class='unselectable'>Image</td>";
							echo "<td style='padding:0px;'><div class='fileSelector'>";
								echo $CategoryItm['icon_vector'];
								echo "<input type='file' accept='.svg' name='icon_vector' id='icon_vector'>";
							echo "</div></td>";
						echo "</tr>";
						echo "<tr>";
							echo "<td class='unselectable'>Fields</td>";
							echo "<td style='padding:0px;'>";
								echo "<table class='FieldTable'>";
									echo "<tr>";
										echo "<th>name</th>";
										echo "<th>type</th>";
										echo "<th>req</th>";
										echo "<th></th>";
									echo "</tr>";
									
									$Fields = json_decode($CategoryItm["data"], true);
									if(count($Fields)==0){
										echo "<tr class='emptyField'>";
											echo "<td colspan='4'>No fields</td>";
										echo "</tr>";
									} else {
										for($i = 0; $i< count($Fields); $i++){
											$addFieldInc++;
											$Itm = $Fields[$i];
											$Name = (isset($Itm['field']))?$Itm['field']:"";
											$Type = (isset($Itm['type']))?$Itm['type']:"smalltext";
											$Required = (isset($Itm['required'])&&$Itm['required']=="on");
											
											echo "<tr>";
												echo "<td>";
													echo "<input type='text' name='field".$i."_field' value='$Name'>";
												echo "</td>";
												echo "<td>";
													echo "<select name='field".$i."_type'>";
														$options = array(
															"calculation" => "Calculation",
															"smalltext" => "Small Text",
															"largetext" => "Large Text",
															"image" => "Image");
														foreach($options as $key => $value){
															($key === $Type)?$chosen = " selected":$chosen = "";
															echo "<option value='$key' $chosen>$value</option>"; 
														}
													echo "</select>";
												echo "</td>";
												echo "<td>";
													echo "<label class='switch'>";
														($Required)?$Checked="checked":$Checked="";
														echo "<input type='checkbox' name='field".$i."_required' $Checked>";
														echo "<span class='slider round'></span>";
													echo "</label>";
												echo "</td>";
												echo "<td>";
													echo "<button class='removeFieldButton'>X</button>";
												echo "</td>";
											echo "</tr>";
										}
									}
									echo "<tr>";
										echo "<td style='padding-right:0px;' colspan='4'>";
											echo "<button class='addFieldButton'>Add Field</button>";
										echo "</td>";
									echo "</tr>";
								echo "</table>";
							echo "</td>";
						echo "</tr>";
						echo "<tr>";
							echo "<td style='padding-right:0px;' align='right' colspan='2'>";
								echo "<button class='removeButton type='submit' formaction='".$BaseURL."Scripts/Category.php?action=Remove'>Remove</button>";
								echo "<button class='saveButton type='submit' formaction='".$BaseURL."Scripts/Category.php?action=Update'>Save</button>";
							echo "</td>";
						echo "</tr>";
					echo "</table>";
				echo "</form>";
			echo "</div>";
		echo "</div>";
	}
	echo "<script>var addFieldInc = $addFieldInc;</script>";
?>

<div class='list_element' id='AddCategoryDiv'>
	<div class='unselectable header'>
		Add Category
	</div>
	<div class='content'>
		<form class='AddCategoryForm' enctype='multipart/form-data' id='AddCategory' method='POST'>
			<table class="CategoryForm">
				<tr>
					<td class='unselectable'>Name</td>
					<td style='padding:0px;'><input type='text' name='name'></td>
				</tr>
				<tr>
					<td class='unselectable'>Image</td>
					<td style='padding:0px;'><input type='file' accept=".svg" name='icon_vector' id='icon_vector'></td>
				</tr>
				<tr>
					<td class='unselectable'>Fields</td>
					<td style='padding:0px;'>
						<table class='FieldTable'>
							<tr>
								<th>name</th>
								<th>type</th>
								<th>req</th>
								<th></th>
							</tr>
							<tr class='emptyField'>
								<td colspan='4'>No fields</td>
							</tr>
							<tr>
								<td style='padding-right:0px;' colspan='4'>
									<button class='addFieldButton'>Add Field</button>
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td style='padding-right:0px;' align='right' colspan='2'>
						<button class='saveButton' onClick='addCategory()'>Save</button>
					</td>
				</tr>
			</table>
		</form>
	</div>
</div>
