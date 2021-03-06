<?php
/*
Gibbon, Flexible & Open School System
Copyright (C) 2010, Ross Parker

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

@session_start();
$_SESSION[$guid]['ibPYPUnitsTab'] = 1;

//Module includes
include './modules/IB PYP/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/IB PYP/units_manage_master_add.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo 'You do not have access to this action.';
    echo '</div>';
} else {
    echo "<div class='trail'>";
    echo "<div class='trailHead'><a href='".$_SESSION[$guid]['absoluteURL']."'>Home</a> > <a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_GET['q']).'/'.getModuleEntry($_GET['q'], $connection2, $guid)."'>".getModuleName($_GET['q'])."</a> > <a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_GET['q']).'/units_manage.php&gibbonSchoolYearID='.$_GET['gibbonSchoolYearID']."'>Manage Units</a> > </div><div class='trailEnd'>Add Master Unit</div>";
    echo '</div>';

    $returns = array();
    $editLink = '';
    if (isset($_GET['editID'])) {
        $editLink = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/IB PYP/units_manage_master_edit.php&ibPYPUnitMasterID='.$_GET['editID'].'&gibbonSchoolYearID='.$_GET['gibbonSchoolYearID'];
    }
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], $editLink, $returns);
    }

    $role = getRole($_SESSION[$guid]['gibbonPersonID'], $connection2);
    if ($role != 'Coordinator' and $role != 'Teacher (Curriculum)') { echo "<div class='error'>";
        echo 'You do not have access to this action.';
        echo '</div>';
    } else {
        $gibbonSchoolYearID = $_GET['gibbonSchoolYearID'];
        if ($gibbonSchoolYearID == '') {
            echo "<div class='error'>";
            echo 'You have not specified a school year.';
            echo '</div>';
        } else {
            $step = null;
            if (isset($_GET['step'])) {
                $step = $_GET['step'];
            }
            if ($step != 1 and $step != 2) {
                $step = 1;
            }

            if ($step == 1) {
                ?>
				<form method="post" action="<?php echo $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module']."/units_manage_master_add.php&gibbonSchoolYearID=$gibbonSchoolYearID&step=2" ?>">
					<table class='smallIntBorder' cellspacing='0' style="width: 100%;">
						<?php $bg = '#fff'; ?>
						<tr class='break'>
							<td colspan=2>
								<h3 class='top'>Step 1 - Basics</h3><br/>
							</td>
						</tr>
						<tr>
							<td>
								<b>Unit Name *</b><br/>
							</td>
							<td class="right">
								<input name="unitname" id="unitname" maxlength=50 value="" type="text" style="width: 300px">
								<script type="text/javascript">
									var unitname=new LiveValidation('unitname');
									unitname.add(Validate.Presence);
								 </script>
							</td>
						</tr>
						<tr>
							<td>
								<b>Active *</b><br/>
								<span style="font-size: 90%"><i></i></span>
							</td>
							<td class="right">
								<select name="active" id="active" style="width: 302px">
									<option value="Y">Y</option>
									<option value="N">N</option>
								</select>
							</td>
						</tr>
						<tr>
							<td>
								<b>Course *</b><br/>
								<span style="font-size: 90%"><i>Which course does this unit belong to?<br/></i></span>
							</td>
							<td class="right">
								<select name="gibbonCourseID" id="gibbonCourseID" style="width: 302px">
									<option value="Please select...">Please select...</option>
									<?php
                                    try {
                                        $dataSelect = array('gibbonSchoolYearID' => $gibbonSchoolYearID);
                                        $sqlSelect = 'SELECT * FROM gibbonCourse WHERE gibbonSchoolYearID=:gibbonSchoolYearID ORDER BY nameShort';
                                        $resultSelect = $connection2->prepare($sqlSelect);
                                        $resultSelect->execute($dataSelect);
                                    } catch (PDOException $e) {
                                    }
									while ($rowSelect = $resultSelect->fetch()) {
										echo "<option value='".$rowSelect['gibbonCourseID']."'>".$rowSelect['nameShort'].'</option>';
									}
									?>
								</select>
								<script type="text/javascript">
									var gibbonCourseID=new LiveValidation('gibbonCourseID');
									gibbonCourseID.add(Validate.Exclusion, { within: ['Please select...'], failureMessage: "Select something!"});
								 </script>
							</td>
						</tr>
						<tr>
							<td>
								<span style="font-size: 90%"><i>* denotes a required field</i></span>
							</td>
							<td class="right" colspan=2>
								<script type="text/javascript">
									$(document).ready(function(){
										$("#submit").click(function(){
											$("#blockCount").val(count) ;
										 });
									});
								</script>
								<input name="blockCount" id=blockCount value="5" type="hidden">
								<input type="hidden" name="address" value="<?php echo $_SESSION[$guid]['address'] ?>">
								<input type="submit" value="Submit">
							</td>
						</tr>
					</table>
				</form>
			<?php

            } else {
                ?>
				<form method="post" action="<?php echo $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module']."/units_manage_master_addProcess.php?gibbonSchoolYearID=$gibbonSchoolYearID" ?>">
					<table class='smallIntBorder' cellspacing='0' style="width: 100%;">
						<tr class='break'>
							<td colspan=3>
								<h3 class='top'>Step 2 - Details</h3><br/>
							</td>
						</tr>
						<tr>
							<td style='background: none; background-color: <?php echo $bg ?>'></td>
							<td>
								<b>Unit Name *</b><br/>
								<span style="font-size: 90%"><i>This value cannot be changed.</i></span>
							</td>
							<td class="right">
								<input readonly name="unitname" id="unitname" maxlength=50 value="<?php echo $_POST['unitname'] ?>" type="text" style="width: 300px">
							</td>
						</tr>
						<tr>
							<td style='background: none; background-color: <?php echo $bg ?>'></td>
							<td>
								<b>Active *</b><br/>
								<span style="font-size: 90%"><i>This value cannot be changed.</i></span>
							</td>
							<td class="right">
								<input readonly name="active" id="active" maxlength=50 value="<?php echo $_POST['active'] ?>" type="text" style="width: 300px">
							</td>
						</tr>
						<tr>
							<td style='background: none; background-color: <?php echo $bg ?>'></td>
							<td>
								<b>Course *</b><br/>
								<span style="font-size: 90%"><i>This value cannot be changed.</i></span>
							</td>
							<td class="right">
								<input name="gibbonCourseID" id="gibbonCourseID" value="<?php echo $_POST['gibbonCourseID'] ?>" type="hidden" style="width: 300px">
								<?php
                                try {
                                    $dataSelect = array('gibbonCourseID' => $_POST['gibbonCourseID']);
                                    $sqlSelect = 'SELECT * FROM gibbonCourse WHERE gibbonCourseID=:gibbonCourseID';
                                    $resultSelect = $connection2->prepare($sqlSelect);
                                    $resultSelect->execute($dataSelect);
                                } catch (PDOException $e) {
                                }
								if ($resultSelect->rowCount() == 1) {
									$rowSelect = $resultSelect->fetch();
									$gibbonYearGroupIDList = $rowSelect['gibbonYearGroupIDList'];
									$gibbonDepartmentID = $rowSelect['gibbonDepartmentID'];
									echo '<input readonly name="course" id="course" value="'.$rowSelect['nameShort'].'" type="text" style="width: 300px">';
								}
								?>
							</td>
						</tr>
						<tr>
							<td style='padding-top: 20px; background: none; background-color: <?php echo $bg ?>'></td>
							<td style='padding-top: 20px'>
								<b>Section Menu</b><br/>
								<a href='#1'>1. What is our purpose?</a><br/>
								<a href='#2'>2. What do we want to learn?</a><br/>
								<a href='#3'>3. How might we know what we have learned?</a><br/>
								<a href='#4'>4. How best might we learn?</a><br/>
								<a href='#5'>5. What resources need to be gathered?</a><br/>
							<td style='padding-top: 20px' class="right">

							</td>
						</tr>

						<?php $bg = '#EDC951'; ?>
						<tr class='break'>
							<td style='background: none!important; background-color: <?php echo $bg ?>!important'></td>
							<td colspan=2>
								<a id='1'>
								<h3>1. What Is Our Purpose?</h3><br/>
							</td>
						</tr>
						<tr>
							<td style='background: none; background-color: <?php echo $bg ?>'></td>
							<td colspan=2>
								<div style='font-weight: bold; text-decoration: underline; font-size: 130%'>Transdisciplinary Theme</div>
								<?php echo getEditor($guid,  $connection2, 'theme', '', 30) ?>
							</td>
						</tr>
						<tr>
							<td style='background: none; background-color: <?php echo $bg ?>'></td>
							<td colspan=2>
								<div style='margin-top: 40px; font-weight: bold; text-decoration: underline; font-size: 130%'>Central Idea</div>
								<?php echo getEditor($guid,  $connection2, 'centralIdea', '', 30) ?>
							</td>
						</tr>
						<tr>
							<td style='background: none; background-color: <?php echo $bg ?>'></td>
							<td colspan=2>
								<div style='margin-top: 40px;font-weight: bold; text-decoration: underline; font-size: 130%'>Outcomes</div>
								<p>What would you like students to accomplish in this unit? These outcomes are drawn from the system-wide collection stored in the Planner module.</p>
							</td>
						</tr>

						<?php
                        $type = 'outcome';
						$allowOutcomeEditing = getSettingByScope($connection2, 'Planner', 'allowOutcomeEditing');
						$categories = array();
						$categoryCount = 0;
						?>
						<style>
							#<?php echo $type ?> { list-style-type: none; margin: 0; padding: 0; width: 100%; }
							#<?php echo $type ?> div.ui-state-default { margin: 0 0px 5px 0px; padding: 5px; font-size: 100%; min-height: 72px; }
							div.ui-state-default_dud { margin: 5px 0px 5px 0px; padding: 5px; font-size: 100%; min-height: 72px; }
							html>body #<?php echo $type ?> li { min-height: 72px; line-height: 1.2em; }
							.<?php echo $type ?>-ui-state-highlight { margin-bottom: 5px; min-height: 72px; line-height: 1.2em; width: 100%; }
							.<?php echo $type ?>-ui-state-highlight {border: 1px solid #fcd3a1; background: #fbf8ee url(images/ui-bg_glass_55_fbf8ee_1x400.png) 50% 50% repeat-x; color: #444444; }
						</style>
						<script>
							$(function() {
								$( "#<?php echo $type ?>" ).sortable({
									placeholder: "<?php echo $type ?>-ui-state-highlight";
									axis: 'y'
								});
							});
						</script>
						<tr>
							<td style='background: none; background-color: <?php echo $bg ?>'></td>
							<td colspan=2>
								<div class="outcome" id="outcome" style='width: 100%; padding: 5px 0px 0px 0px; min-height: 72px'>
										<div id="outcomeOuter0">
											<div style='color: #ddd; font-size: 230%; margin: 15px 0 0 6px'>Key outcomes listed here...</div>
										</div>
									</div>
								<div style='width: 100%; padding: 0px 0px 0px 0px; border-bottom: 1px solid #333'>
									<div class="ui-state-default_dud" style='padding: 0px; height: 60px'>
										<table cellspacing='0' style='width: 100%'>
											<tr>
												<td style='width: 50%'>
													<script type="text/javascript">
														var outcomeCount=1 ;
														/* Unit type control */
														$(document).ready(function(){
															$("#new").click(function(){

															 });
														});
													</script>
													<select id='newOutcome' onChange='outcomeDisplayElements(this.value);' style='float: none; margin-left: 3px; margin-top: 0px; margin-bottom: 3px; width: 350px'>
														<option class='all' value='0'>Choose an outcome to add it to this unit</option>
														<?php
                                                        $currentCategory = '';
														$lastCategory = '';
														$switchContents = '';
														try {
															$countClause = 0;
															$years = explode(',', $gibbonYearGroupIDList);
															$dataSelect = array();
															$sqlSelect = '';
															foreach ($years as $year) {
																$dataSelect['clause'.$countClause] = '%'.$year.'%';
																$sqlSelect .= "(SELECT * FROM gibbonOutcome WHERE active='Y' AND scope='School' AND gibbonYearGroupIDList LIKE :clause".$countClause.') UNION ';
																++$countClause;
															}
															$resultSelect = $connection2->prepare(substr($sqlSelect, 0, -6).'ORDER BY category, name');
															$resultSelect->execute($dataSelect);
														} catch (PDOException $e) {
															echo "<div class='error'>".$e->getMessage().'</div>';
														}
														echo "<optgroup label='--SCHOOL OUTCOMES--'>";
														while ($rowSelect = $resultSelect->fetch()) {
															$currentCategory = $rowSelect['category'];
															if (($currentCategory != $lastCategory) and $currentCategory != '') {
																echo "<optgroup label='--".$currentCategory."--'>";
																echo "<option class='$currentCategory' value='0'>Choose an outcome to add it to this unit</option>";
																$categories[$categoryCount] = $currentCategory;
																++$categoryCount;
															}
															echo "<option class='all ".$rowSelect['category']."'  value='".$rowSelect['gibbonOutcomeID']."'>".$rowSelect['name'].'</option>';
															$switchContents .= 'case "'.$rowSelect['gibbonOutcomeID'].'": ';
															$switchContents .= "$(\"#outcome\").append('<div id=\'outcomeBlockOuter' + outcomeCount + '\'><img style=\'margin: 10px 0 5px 0\' src=\'".$_SESSION[$guid]['absoluteURL']."/themes/Default/img/loading.gif\' alt=\'Loading\' onclick=\'return false;\' /><br/>Loading</div>');";
															$switchContents .= '$("#outcomeBlockOuter" + outcomeCount).load("'.$_SESSION[$guid]['absoluteURL'].'/modules/IB%20PYP/units_manage_add_blockAjax.php","type=outcome&id=" + outcomeCount + "&title='.urlencode($rowSelect['name'])."\&category=".urlencode($rowSelect['category']).'&ibPYPGlossaryID='.urlencode($rowSelect['gibbonOutcomeID']).'&contents='.urlencode($rowSelect['description']).'&allowOutcomeEditing='.urlencode($allowOutcomeEditing).'") ;';
															$switchContents .= 'outcomeCount++ ;';
															$switchContents .= "$('#newOutcome').val('0');";
															$switchContents .= 'break;';
															$lastCategory = $rowSelect['category'];
														}

														$currentCategory = '';
														$lastCategory = '';
														$currentLA = '';
														$lastLA = '';
														try {
															$countClause = 0;
															$years = explode(',', $gibbonYearGroupIDList);
															$dataSelect = array('gibbonDepartmentID' => $gibbonDepartmentID);
															$sqlSelect = '';
															foreach ($years as $year) {
																$dataSelect['clause'.$countClause] = '%'.$year.'%';
																$sqlSelect .= "(SELECT gibbonOutcome.*, gibbonDepartment.name AS learningArea FROM gibbonOutcome JOIN gibbonDepartment ON (gibbonOutcome.gibbonDepartmentID=gibbonDepartment.gibbonDepartmentID) WHERE active='Y' AND scope='Learning Area' AND gibbonDepartment.gibbonDepartmentID=:gibbonDepartmentID AND gibbonYearGroupIDList LIKE :clause".$countClause.') UNION ';
																++$countClause;
															}
															$resultSelect = $connection2->prepare(substr($sqlSelect, 0, -6).'ORDER BY learningArea, category, name');
															$resultSelect->execute($dataSelect);
														} catch (PDOException $e) {
															echo "<div class='error'>".$e->getMessage().'</div>';
														}
														while ($rowSelect = $resultSelect->fetch()) {
															$currentCategory = $rowSelect['category'];
															$currentLA = $rowSelect['learningArea'];
															if (($currentLA != $lastLA) and $currentLA != '') {
																echo "<optgroup label='--".strToUpper($currentLA)." OUTCOMES--'>";
															}
															if (($currentCategory != $lastCategory) and $currentCategory != '') {
																echo "<optgroup label='--".$currentCategory."--'>";
																echo "<option class='$currentCategory' value='0'>Choose an outcome to add it to this unit</option>";
																$categories[$categoryCount] = $currentCategory;
																++$categoryCount;
															}
															echo "<option class='all ".$rowSelect['category']."'  value='".$rowSelect['gibbonOutcomeID']."'>".$rowSelect['name'].'</option>';
															$switchContents .= 'case "'.$rowSelect['gibbonOutcomeID'].'": ';
															$switchContents .= "$(\"#outcome\").append('<div id=\'outcomeBlockOuter' + outcomeCount + '\'><img style=\'margin: 10px 0 5px 0\' src=\'".$_SESSION[$guid]['absoluteURL']."/themes/Default/img/loading.gif\' alt=\'Loading\' onclick=\'return false;\' /><br/>Loading</div>');";
															$switchContents .= '$("#outcomeBlockOuter" + outcomeCount).load("'.$_SESSION[$guid]['absoluteURL'].'/modules/IB%20PYP/units_manage_add_blockAjax.php","type=outcome&id=" + outcomeCount + "&title='.urlencode($rowSelect['name'])."\&category=".urlencode($rowSelect['category']).'&ibPYPGlossaryID='.urlencode($rowSelect['gibbonOutcomeID']).'&contents='.urlencode($rowSelect['description']).'&allowOutcomeEditing='.urlencode($allowOutcomeEditing).'") ;';
															$switchContents .= 'outcomeCount++ ;';
															$switchContents .= "$('#newOutcome').val('0');";
															$switchContents .= 'break;';
															$lastCategory = $rowSelect['category'];
															$lastLA = $rowSelect['learningArea'];
														}

														?>
													</select><br/>
													<?php
                                                    if (count($categories) > 0) {
                                                        ?>
														<select id='outcomeFilter' style='float: none; margin-left: 3px; margin-top: 0px; width: 350px'>
															<option value='all'>View All</option>
															<?php
                                                            $categories = array_unique($categories);
                                                        $categories = msort($categories);
                                                        foreach ($categories as $category) {
                                                            echo "<option value='$category'>$category</option>";
                                                        }
                                                        ?>
														</select>
														<script type="text/javascript">
															$("#newOutcome").chainedTo("#outcomeFilter");
														</script>
														<?php

                                                    }
                									?>
													<script type='text/javascript'>
														var <?php echo $type ?>Used=new Array();
														var <?php echo $type ?>UsedCount=0 ;

														function outcomeDisplayElements(number) {
															$("#<?php echo $type ?>Outer0").css("display", "none") ;
															if (<?php echo $type ?>Used.indexOf(number)<0) {
																<?php echo $type ?>Used[<?php echo $type ?>UsedCount]=number ;
																<?php echo $type ?>UsedCount++ ;
																switch(number) {
																	<?php echo $switchContents ?>
																}
															}
															else {
																alert("This element has already been selected!") ;
																$('#newOutcome').val('0');
															}
														}
													</script>
												</td>
											</tr>
										</table>
									</div>
								</div>
							</td>
						</tr>
						<tr>
							<td style='background: none; background-color: <?php echo $bg ?>'></td>
							<td colspan=2>
								<div style='margin-top: 40px; font-weight: bold; text-decoration: underline; font-size: 130%'>Summative Assessment</div>
								<p>What are the possible ways of assessing students’ understanding of the central idea? What evidence, including student initiated actions will we look for?</p>
								<?php echo getEditor($guid,  $connection2, 'summativeAssessment', '', 30, true) ?>
							</td>
						</tr>

						<?php $bg = '#6A4A3C'; ?>
						<tr class='break'>
							<td style='background: none!important; background-color: <?php echo $bg ?>!important'></td>
							<td colspan=2>
								<a id='2'>
								<h3>2. What Do We Want To Learn?</h3><br/>
							</td>
						</tr>
						<tr>
							<td style='background: none; background-color: <?php echo $bg ?>'></td>
							<td colspan=2>
								<div style='font-weight: bold; text-decoration: underline; font-size: 130%'>Key Concepts</div>
								<p>What are the key concepts to be emphasized within this inquiry?</p>
							</td>
						</tr>

						<?php $type = 'concept'; ?>
						<style>
							#<?php echo $type ?> { list-style-type: none; margin: 0; padding: 0; width: 100%; }
							#<?php echo $type ?> div.ui-state-default { margin: 0 0px 5px 0px; padding: 5px; font-size: 100%; min-height: 72px; }
							div.ui-state-default_dud { margin: 5px 0px 5px 0px; padding: 5px; font-size: 100%; min-height: 72px; }
							html>body #<?php echo $type ?> li { min-height: 72px; line-height: 1.2em; }
							.<?php echo $type ?>-ui-state-highlight { margin-bottom: 5px; min-height: 72px; line-height: 1.2em; width: 100%; }
							.<?php echo $type ?>-ui-state-highlight {border: 1px solid #fcd3a1; background: #fbf8ee url(images/ui-bg_glass_55_fbf8ee_1x400.png) 50% 50% repeat-x; color: #444444; }
						</style>
						<script>
							$(function() {
								$( "#<?php echo $type ?>" ).sortable({
									placeholder: "<?php echo $type ?>-ui-state-highlight";
									axis: 'y'
								});
							});
						</script>
						<tr>
							<td style='background: none; background-color: <?php echo $bg ?>'></td>
							<td colspan=2>
								<div class="concept" id="concept" style='width: 100%; padding: 5px 0px 0px 0px; min-height: 72px'>
										<div id="conceptOuter0">
											<div style='color: #ddd; font-size: 230%; margin: 15px 0 0 6px'>Key concepts listed here...</div>
										</div>
									</div>
								<div style='width: 100%; padding: 0px 0px 0px 0px; border-bottom: 1px solid #333'>
									<div class="ui-state-default_dud" style='padding: 0px; height: 60px'>
										<table cellspacing='0' style='width: 100%'>
											<tr style='height: 60px'>
												<td style='width: 50%'>
													<script type="text/javascript">
														var conceptCount=1 ;
														/* Unit type control */
														$(document).ready(function(){
															$("#new").click(function(){

															 });
														});
													</script>
													<select id='newConcept' onChange='conceptDisplayElements(this.value);' style='float: none; margin-left: 3px; margin-top: 0px; width: 350px'>
														<option value='0'>Choose a concept to add it to this unit</option>
														<?php
                                                        $currentCategory = '';
														$lastCategory = '';
														$switchContents = '';
														try {
															$dataSelect = array();
															$sqlSelect = "SELECT * FROM ibPYPGlossary WHERE type='Concept' ORDER BY category, title";
															$resultSelect = $connection2->prepare($sqlSelect);
															$resultSelect->execute($dataSelect);
														} catch (PDOException $e) {
															echo "<div class='error'>".$e->getMessage().'</div>';
														}

														while ($rowSelect = $resultSelect->fetch()) {
															$currentCategory = $rowSelect['category'];
															if (($currentCategory != $lastCategory) and $currentCategory != '') {
																echo "<optgroup label='--".$currentCategory."--'>";
															}
															echo "<option value='".$rowSelect['ibPYPGlossaryID']."'>".$rowSelect['title'].'</option>';
															$switchContents .= 'case "'.$rowSelect['ibPYPGlossaryID'].'": ';
															$switchContents .= "$(\"#concept\").append('<div id=\'conceptBlockOuter' + conceptCount + '\'><img style=\'margin: 10px 0 5px 0\' src=\'".$_SESSION[$guid]['absoluteURL']."/themes/Default/img/loading.gif\' alt=\'Loading\' onclick=\'return false;\' /><br/>Loading</div>');";
															$switchContents .= '$("#conceptBlockOuter" + conceptCount).load("'.$_SESSION[$guid]['absoluteURL'].'/modules/IB%20PYP/units_manage_add_blockAjax.php","type=concept&id=" + conceptCount + "&title='.urlencode($rowSelect['title'])."\&category=".urlencode($rowSelect['category']).'&ibPYPGlossaryID='.urlencode($rowSelect['ibPYPGlossaryID']).'&contents='.urlencode($rowSelect['content']).'") ;';
															$switchContents .= 'conceptCount++ ;';
															$switchContents .= "$('#newConcept').val('0');";
															$switchContents .= 'break;';
															$lastCategory = $rowSelect['category'];
														}
														?>
													</select>
													<script type='text/javascript'>
														var <?php echo $type ?>Used=new Array();
														var <?php echo $type ?>UsedCount=0 ;

														function conceptDisplayElements(number) {
															$("#<?php echo $type ?>Outer0").css("display", "none") ;
															if (<?php echo $type ?>Used.indexOf(number)<0) {
																<?php echo $type ?>Used[<?php echo $type ?>UsedCount]=number ;
																<?php echo $type ?>UsedCount++ ;
																switch(number) {
																	<?php echo $switchContents ?>
																}
															}
															else {
																alert("This element has already been selected!") ;
																$('#newConcept').val('0');
															}
														}
													</script>
												</td>
											</tr>
										</table>
									</div>
								</div>
							</td>
						</tr>

						<tr>
							<td style='background: none; background-color: <?php echo $bg ?>'></td>
							<td colspan=2>
								<div style='margin-top: 40px; font-weight: bold; text-decoration: underline; font-size: 130%'>Related Concepts</div>
								<p>What are the concepts that are related to this inquiry?</p>
								<?php echo getEditor($guid,  $connection2, 'relatedConcepts', '<ul><li></li><li></li><li></li></ul>', 10) ?>
							</td>
						</tr>

						<tr>
							<td style='background: none; background-color: <?php echo $bg ?>'></td>
							<td colspan=2>
								<div style='margin-top: 40px; font-weight: bold; text-decoration: underline; font-size: 130%'>Lines of Inquiry</div>
								<p>What lines of inquiry will define the scope of the inquiry into the central idea?</p>
								<?php echo getEditor($guid,  $connection2, 'linesOfInquiry', '<ul><li></li><li></li><li></li></ul>', 10) ?>
							</td>
						</tr>
						<tr>
							<td style='background: none; background-color: <?php echo $bg ?>'></td>
							<td colspan=2>
								<div style='margin-top: 40px; font-weight: bold; text-decoration: underline; font-size: 130%'>Teacher Questions<br/></div>
								<p>What teacher questions will drive these inquiries?<br/><br/></p>
								<?php echo getEditor($guid,  $connection2, 'teacherQuestions', '<ol><li></li><li></li><li></li></ol>', 10) ?>
							</td>
						</tr>

						<tr>
							<td style='background: none; background-color: <?php echo $bg ?>'></td>
							<td colspan=2>
								<div style='margin-top: 40px; font-weight: bold; text-decoration: underline; font-size: 130%'>Provocation</div>
								<?php echo getEditor($guid,  $connection2, 'provocation', '', 30, true, false, false, true, 'purpose=Provocation', true) ?>
							</td>
						</tr>

						<?php $bg = '#00A0B0'; ?>
						<tr class='break'>
							<td style='background: none!important; background-color: <?php echo $bg ?>!important'></td>
							<td colspan=2>
								<a id='3'>
								<h3>3. How Might We Know What We Have Learned?</h3><br/>
							</td>
						</tr>
						<tr>
							<td style='background: none; background-color: <?php echo $bg ?>'></td>
							<td colspan=2>
								<div style='font-weight: bold; text-decoration: underline; font-size: 130%'>Assessing Prior Knowledge & Skills</div>
								<p>What are the possible ways of assessing students’ prior knowledge and skills? What evidence will we look for? </p>
								<?php echo getEditor($guid,  $connection2, 'preAssessment', '', 30, true, false, false, true, 'purpose=Assessment%20Aid', true) ?>
							</td>
						</tr>
						<tr>
							<td style='background: none; background-color: <?php echo $bg ?>'></td>
							<td colspan=2>
								<div style='margin-top: 40px; font-weight: bold; text-decoration: underline; font-size: 130%'>Formative Assessment</div>
								<p>What are the possible ways of assessing student learning in the context of the lines of inquiry? What evidence will we look for?</p>
								<?php echo getEditor($guid,  $connection2, 'formativeAssessment', '', 30, true, false, false, true, 'purpose=Assessment%20Aid', true) ?>
							</td>
						</tr>

						<?php $bg = '#C44D58'; ?>
						<tr class='break'>
							<td style='background: none!important; background-color: <?php echo $bg ?>!important'></td>
							<td colspan=2>
								<a id='4'>
								<h3>4. How Best Might We Learn?</h3><br/>
							</td>
						</tr>
						<tr>
							<td style='background: none; background-color: <?php echo $bg ?>'></td>
							<td colspan=2>
								<div style='font-weight: bold; text-decoration: underline; font-size: 130%'>Learning Experiences</div>
							</td>
						</tr>
						<tr>
							<td style='background: none; background-color: <?php echo $bg ?>'></td>
							<td colspan=2>
								<p style='color: black'>Smart content blocks are Gibbon's way of helping you organise and manage the content in your units. <b>These blocks are shared across the master unit, and all of it's working units: so, changes here are collaborative, and will impact other version of this unit.</p>

								<style>
									#sortable { list-style-type: none; margin: 0; padding: 0; width: 100%; }
									#sortable div.ui-state-default { margin: 0 0px 5px 0px; padding: 5px; font-size: 100%; min-height: 72px; }
									div.ui-state-default_dud { margin: 5px 0px 5px 0px; padding: 5px; font-size: 100%; min-height: 72px; }
									html>body #sortable li { min-height: 72px; line-height: 1.2em; }
									#sortable .ui-state-highlight { margin-bottom: 5px; min-height: 72px; line-height: 1.2em; width: 100%; }
								</style>
								<script>
									$(function() {
										$( "#sortable" ).sortable({
											placeholder: "ui-state-highlight";
											axis: 'y'
										});
									});
								</script>

								<div class="sortable" id="sortable" style='width: 100%; padding: 5px 0px 0px 0px; border-top: 1px solid #333; border-bottom: 1px solid #333'>
									<?php
                                    for ($i = 1; $i <= 5; ++$i) {
                                        makeBlock($guid, $connection2, $i);
                                    }
                					?>
								</div>

								<div style='width: 100%; padding: 0px 0px 0px 0px; border-bottom: 1px solid #333'>
									<div class="ui-state-default_dud odd" style='padding: 0px;'>
										<table cellspacing='0' style='width: 100%'>
											<tr style='height: 60px'>
												<td style='width: 50%'>
													<script type="text/javascript">
														var count=6 ;
														/* Unit type control */
														$(document).ready(function(){
															$("#new").click(function(){
																$("#sortable").append('<div id=\'blockOuter' + count + '\'><img style=\'margin: 10px 0 5px 0\' src=\'<?php echo $_SESSION[$guid]['absoluteURL'] ?>/themes/Default/img/loading.gif\' alt=\'Loading\' onclick=\'return false;\' /><br/>Loading</div>');
																$("#blockOuter" + count).load("<?php echo $_SESSION[$guid]['absoluteURL'] ?>/modules/Planner/units_add_blockAjax.php","id=" + count) ;
																count++ ;
															 });
														});
													</script>
													<div id='new' style='cursor: default; float: none; border: 1px dotted #aaa; background: none; margin-left: 3px; color: #999; margin-top: 0px; font-size: 140%; font-weight: bold; width: 350px'>Click to create a new block</div><br/>
												</td>
											</tr>
										</table>
									</div>
								</div>
							</td>
						</tr>
						<tr>
							<td style='background: none; background-color: <?php echo $bg ?>'></td>
							<td colspan=2>
								<div style='margin-top: 40px; font-weight: bold; text-decoration: underline; font-size: 130%'>Transdisciplinary Skills</div>
								<p>What opportunities will occur for transdisciplinary skills?</p>
							</td>
						</tr>
						<tr>
							<td style='background: none; background-color: <?php echo $bg ?>'></td>
							<?php $type = 'skills'; ?>
							<style>
								#<?php echo $type ?> { list-style-type: none; margin: 0; padding: 0; width: 100%; }
								#<?php echo $type ?> div.ui-state-default { margin: 0 0px 5px 0px; padding: 5px; font-size: 100%; min-height: 72px; }
								div.ui-state-default_dud { margin: 5px 0px 5px 0px; padding: 5px; font-size: 100%; min-height: 72px; }
								html>body #<?php echo $type ?> li { min-height: 72px; line-height: 1.2em; }
								.<?php echo $type ?>-ui-state-highlight { margin-bottom: 5px; min-height: 72px; line-height: 1.2em; width: 100%; }
								.<?php echo $type ?>-ui-state-highlight {border: 1px solid #fcd3a1; background: #fbf8ee url(images/ui-bg_glass_55_fbf8ee_1x400.png) 50% 50% repeat-x; color: #444444; }
							</style>
							<script>
								$(function() {
									$( "#<?php echo $type ?>" ).sortable({
										placeholder: "<?php echo $type ?>-ui-state-highlight";
										axis: 'y'
									});
								});
							</script>
							<td colspan=2>
								<div class="skills" id="skills" style='width: 100%; padding: 5px 0px 0px 0px; min-height: 72px'>
									<div id="skillsOuter0">
										<div style='color: #ddd; font-size: 230%; margin: 15px 0 0 6px'>Transdisciplinary Skills listed here...</div>
									</div>
								</div>
								<div style='width: 100%; padding: 0px 0px 0px 0px; border-bottom: 1px solid #333'>
									<div class="ui-state-default_dud" style='padding: 0px; height: 60px'>
										<table cellspacing='0' style='width: 100%'>
											<tr style='height: 60px'>
												<td style='width: 50%'>
													<script type="text/javascript">
														var skillsCount=1 ;
														/* Unit type control */
														$(document).ready(function(){
															$("#new").click(function(){

															 });
														});
													</script>
													<select id='newSkill' onChange='skillsDisplayElements(this.value);' style='float: none; margin-left: 3px; margin-top: 0px; width: 350px'>
														<option value='0'>Choose a skill to add it to this unit</option>
														<?php
                                                        $currentCategory = '';
														$lastCategory = '';
														$switchContents = '';
														try {
															$dataSelect = array();
															$sqlSelect = "SELECT * FROM ibPYPGlossary WHERE type='Transdisciplinary Skill' ORDER BY category, title";
															$resultSelect = $connection2->prepare($sqlSelect);
															$resultSelect->execute($dataSelect);
														} catch (PDOException $e) {
															echo "<div class='error'>".$e->getMessage().'</div>';
														}

														while ($rowSelect = $resultSelect->fetch()) {
															$currentCategory = $rowSelect['category'];
															if (($currentCategory != $lastCategory) and $currentCategory != '') {
																echo "<optgroup label='--".$currentCategory."--'>";
															}
															echo "<option value='".$rowSelect['ibPYPGlossaryID']."'>".$rowSelect['title'].'</option>';
															$switchContents .= 'case "'.$rowSelect['ibPYPGlossaryID'].'": ';
															$switchContents .= "$(\"#skills\").append('<div id=\'skillsBlockOuter' + skillsCount + '\'><img style=\'margin: 10px 0 5px 0\' src=\'".$_SESSION[$guid]['absoluteURL']."/themes/Default/img/loading.gif\' alt=\'Loading\' onclick=\'return false;\' /><br/>Loading</div>');";
															$switchContents .= '$("#skillsBlockOuter" + skillsCount).load("'.$_SESSION[$guid]['absoluteURL'].'/modules/IB%20PYP/units_manage_add_blockAjax.php","type=skills&id=" + skillsCount + "&title='.urlencode($rowSelect['title'])."\&category=".urlencode($rowSelect['category']).'&ibPYPGlossaryID='.urlencode($rowSelect['ibPYPGlossaryID']).'&contents='.urlencode($rowSelect['content']).'") ;';
															$switchContents .= 'skillsCount++ ;';
															$switchContents .= "$('#newSkill').val('0');";
															$switchContents .= 'break;';
															$lastCategory = $rowSelect['category'];
														}
														?>
													</select>
													<script type='text/javascript'>
														var <?php echo $type ?>Used=new Array();
														var <?php echo $type ?>UsedCount=0 ;

														function skillsDisplayElements(number) {
															$("#<?php echo $type ?>Outer0").css("display", "none") ;
															if (<?php echo $type ?>Used.indexOf(number)<0) {
																<?php echo $type ?>Used[<?php echo $type ?>UsedCount]=number ;
																<?php echo $type ?>UsedCount++ ;
																switch(number) {
																	<?php echo $switchContents ?>
																}
															}
															else {
																alert("This element has already been selected!") ;
																$('#newSkill').val('0');
															}
														}
													</script>
												</td>
											</tr>
										</table>
									</div>
								</div>
							</td>
						</tr>
						<tr>
							<td style='background: none; background-color: <?php echo $bg ?>'></td>
							<td colspan=2>
								<div style='margin-top: 40px; font-weight: bold; text-decoration: underline; font-size: 130%'>Learner Profile & Attitudes</div>
								<p>What opportunity will occur for the development of the attributes of the learner profile and attitudes?</p>
							</td>
						</tr>
						<tr>
							<td style='background: none; background-color: <?php echo $bg ?>'></td>
							<?php $type = 'learnerProfile'; ?>
							<style>
								#<?php echo $type ?> { list-style-type: none; margin: 0; padding: 0; width: 100%; }
								#<?php echo $type ?> div.ui-state-default { margin: 0 0px 5px 0px; padding: 5px; font-size: 100%; min-height: 72px; }
								div.ui-state-default_dud { margin: 5px 0px 5px 0px; padding: 5px; font-size: 100%; min-height: 72px; }
								html>body #<?php echo $type ?> li { min-height: 72px; line-height: 1.2em; }
								.<?php echo $type ?>-ui-state-highlight { margin-bottom: 5px; min-height: 72px; line-height: 1.2em; width: 100%; }
								.<?php echo $type ?>-ui-state-highlight {border: 1px solid #fcd3a1; background: #fbf8ee url(images/ui-bg_glass_55_fbf8ee_1x400.png) 50% 50% repeat-x; color: #444444; }
							</style>
							<script>
								$(function() {
									$( "#<?php echo $type ?>" ).sortable({
										placeholder: "<?php echo $type ?>-ui-state-highlight";
										axis: 'y'
									});
								});
							</script>
							<td colspan=2>
								<div class="learnerProfile" id="learnerProfile" style='width: 100%; padding: 5px 0px 0px 0px; min-height: 72px'>
									<div id="learnerProfileOuter0">
										<div style='color: #ddd; font-size: 230%; margin: 15px 0 0 6px'>Learner Profile & Attitudes listed here...</div>
									</div>
								</div>
								<div style='width: 100%; padding: 0px 0px 0px 0px; border-bottom: 1px solid #333'>
									<div class="ui-state-default_dud" style='padding: 0px; height: 60px'>
										<table cellspacing='0' style='width: 100%'>
											<tr style='height: 60px'>
												<td style='width: 50%'>
													<script type="text/javascript">
														var learnerProfileCount=1 ;
														/* Unit type control */
														$(document).ready(function(){
															$("#new").click(function(){

															 });
														});
													</script>
													<select id='newLearnerProfile' onChange='learnerProfileDisplayElements(this.value);' style='float: none; margin-left: 3px; margin-top: 0px; width: 350px'>
														<option value='0'>Choose a learner profile or attitude to add it to this unit</option>
														<?php
                                                        $currentType = '';
														$lastType = '';
														$switchContents = '';
														try {
															$dataSelect = array();
															$sqlSelect = "SELECT * FROM ibPYPGlossary WHERE type='Learner Profile' OR type='Attitude' ORDER BY type, category, title";
															$resultSelect = $connection2->prepare($sqlSelect);
															$resultSelect->execute($dataSelect);
														} catch (PDOException $e) {
															echo "<div class='error'>".$e->getMessage().'</div>';
														}

														while ($rowSelect = $resultSelect->fetch()) {
															$currentType = $rowSelect['type'];
															if (($currentType != $lastType) and $currentType != '') {
																echo "<optgroup label='--".$currentType."--'>";
															}
															echo "<option value='".$rowSelect['ibPYPGlossaryID']."'>".$rowSelect['title'].'</option>';
															$switchContents .= 'case "'.$rowSelect['ibPYPGlossaryID'].'": ';
															$switchContents .= "$(\"#learnerProfile\").append('<div id=\'learnerProfileBlockOuter' + learnerProfileCount + '\'><img style=\'margin: 10px 0 5px 0\' src=\'".$_SESSION[$guid]['absoluteURL']."/themes/Default/img/loading.gif\' alt=\'Loading\' onclick=\'return false;\' /><br/>Loading</div>');";
															$switchContents .= '$("#learnerProfileBlockOuter" + learnerProfileCount).load("'.$_SESSION[$guid]['absoluteURL'].'/modules/IB%20PYP/units_manage_add_blockAjax.php","type=learnerProfile&id=" + learnerProfileCount + "&title='.urlencode($rowSelect['title'])."\&category=".urlencode($rowSelect['category']).'&ibPYPGlossaryID='.urlencode($rowSelect['ibPYPGlossaryID']).'&contents='.urlencode($rowSelect['content']).'") ;';
															$switchContents .= 'learnerProfileCount++ ;';
															$switchContents .= "$('#newLearnerProfile').val('0');";
															$switchContents .= 'break;';
															$lastType = $rowSelect['type'];
														}
														?>
													</select>
													<script type='text/javascript'>
														var <?php echo $type ?>Used=new Array();
														var <?php echo $type ?>UsedCount=0 ;

														function learnerProfileDisplayElements(number) {
															$("#<?php echo $type ?>Outer0").css("display", "none") ;
															if (<?php echo $type ?>Used.indexOf(number)<0) {
																<?php echo $type ?>Used[<?php echo $type ?>UsedCount]=number ;
																<?php echo $type ?>UsedCount++ ;
																switch(number) {
																	<?php echo $switchContents ?>
																}
															}
															else {
																alert("This element has already been selected!") ;
																$('#newLearnerProfile').val('0');
															}
														}
													</script>

												</td>
											</tr>
										</table>
									</div>
								</div>
							</td>
						</tr>

						<?php $bg = '#EB6841'; ?>
						<tr class='break'>
							<td style='background: none!important; background-color: <?php echo $bg ?>!important'></td>
							<td colspan=2>
								<a id='5'>
								<h3>5. What Resources Need To Be Gathered?</h3><br/>
							</td>
						</tr>
						<tr>
							<td style='background: none; background-color: <?php echo $bg ?>'></td>
							<td colspan=2>
								<div style='font-weight: bold; text-decoration: underline; font-size: 130%'>Resources</div>
								<p>What people, places, audio-visual materials, related literature, music, art, computer software etc will be available?</p>
								<?php echo getEditor($guid,  $connection2, 'resources', '', 30, true, false, false, true, '', true) ?>
							</td>
						</tr>
						<tr>
							<td style='background: none; background-color: <?php echo $bg ?>'></td>
							<td colspan=2>
								<div style='margin-top: 40px; font-weight: bold; text-decoration: underline; font-size: 130%'>Action</div>
								<p>What possible action could be inspired by this inquiry?</p>
								<?php echo getEditor($guid,  $connection2, 'action', '', 30, false, false, false, true, '', true) ?>
							</td>
						</tr>
						<tr>
							<td style='background: none; background-color: <?php echo $bg ?>'></td>
							<td colspan=2>
								<div style='margin-top: 40px; font-weight: bold; text-decoration: underline; font-size: 130%'>Classroom Environment</div>
								<p>How will the classroom environment, local environment and or community be used to facilitate the inquiry? </p>
								<?php echo getEditor($guid,  $connection2, 'environments', '', 30) ?>
							</td>
						</tr>

						<tr>
							<td colspan=2>
								<span style="font-size: 90%"><i>* denotes a required field</i></span>
							</td>
							<td class="right">
								<script type="text/javascript">
									$(document).ready(function(){
										$("#submit").click(function(){
											$("#blockCount").val(count) ;
										 });
									});
								</script>
								<input name="blockCount" id=blockCount value="5" type="hidden">
								<input type="hidden" name="address" value="<?php echo $_SESSION[$guid]['address'] ?>">
								<input type="submit" value="Submit">
							</td>
						</tr>
					</table>
				</form>
				<?php

            }
        }
    }
}
?>
