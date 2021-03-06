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

include '../../functions.php';
include '../../config.php';

include './moduleFunctions.php';

//New PDO DB connection
try {
    $connection2 = new PDO("mysql:host=$databaseServer;dbname=$databaseName", $databaseUsername, $databasePassword);
    $connection2->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $connection2->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo $e->getMessage();
}

@session_start();
$_SESSION[$guid]['ibPYPUnitsTab'] = 1;

//Set timezone from session variable
date_default_timezone_set($_SESSION[$guid]['timezone']);

$gibbonSchoolYearID = $_POST['gibbonSchoolYearID'];
$ibPYPUnitMasterID = $_POST['ibPYPUnitMasterID'];
$classes = null;
if (isset($_POST['classes'])) {
    $classes = $_POST['classes'];
}
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/units_manage_master_deploy.php&ibPYPUnitMasterID=$ibPYPUnitMasterID&gibbonSchoolYearID=$gibbonSchoolYearID";
$URLSuccess = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/units_manage.php&gibbonSchoolYearID=$gibbonSchoolYearID";

if (isActionAccessible($guid, $connection2, '/modules/IB PYP/units_manage_master_deploy.php') == false) {
    //Fail 0
    $URL = $URL.'&return=error0';
    header("Location: {$URL}");
} else {
    $role = getRole($_SESSION[$guid]['gibbonPersonID'], $connection2);
    if ($role == false) {
        //Fail 0
        $URL = $URL."&return=error0$params";
        header("Location: {$URL}");
    } else {
        if ($role != 'Coordinator' and $role != 'Teacher (Curriculum)') {
            //Fail 0
            $URL = $URL."&return=error0$params";
            header("Location: {$URL}");
        } else {
            //Proceed!
            //Validate Inputs
            if ($gibbonSchoolYearID == '' or $ibPYPUnitMasterID == '' or count($classes) < 1) {
                //Fail 3
                $URL = $URL.'&return=error3';
                header("Location: {$URL}");
            } else {
                //Check access to specified course
                try {
                    $data = array('ibPYPUnitMasterID' => $ibPYPUnitMasterID);
                    $sql = 'SELECT * FROM ibPYPUnitMaster WHERE ibPYPUnitMasterID=:ibPYPUnitMasterID';
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    //Fail2
                    $URL = $URL.'&deleteReturn=error2';
                    header("Location: {$URL}");
                    exit();
                }

                if ($result->rowCount() != 1) {
                    //Fail 4
                    $URL = $URL.'&return=error4';
                    header("Location: {$URL}");
                } else {
                    $row = $result->fetch();

                    //Lock table
                    try {
                        $sql = 'LOCK TABLES ibPYPUnitWorking WRITE, ibPYPUnitWorkingBlock WRITE, ibPYPUnitMasterBlock WRITE, ibPYPUnitWorkingSmartBlock WRITE, ibPYPUnitMasterSmartBlock WRITE';
                        $result = $connection2->query($sql);
                    } catch (PDOException $e) {
                        //Fail 2
                        $URL = $URL.'&return=error2';
                        header("Location: {$URL}");
                        exit();
                    }

                    $partialFail = false;

                    //Create required copies
                    foreach ($classes as $class) {
                        //Check for existing deployment
                        try {
                            $dataCheck = array('ibPYPUnitMasterID' => $ibPYPUnitMasterID, 'gibbonCourseClassID' => $class);
                            $sqlCheck = 'SELECT * FROM ibPYPUnitWorking WHERE ibPYPUnitMasterID=:ibPYPUnitMasterID AND gibbonCourseClassID=:gibbonCourseClassID';
                            $resultCheck = $connection2->prepare($sqlCheck);
                            $resultCheck->execute($dataCheck);
                        } catch (PDOException $e) {
                        }
                        if ($resultCheck->rowCount() > 0) {
                            $partialFail = true;
                        } else {
                            //Get next autoincrement
                            try {
                                $dataAI = array();
                                $sqlAI = "SHOW TABLE STATUS LIKE 'ibPYPUnitWorking'";
                                $resultAI = $connection2->prepare($sqlAI);
                                $resultAI->execute($dataAI);
                            } catch (PDOException $e) {
                                //Fail 2
                                $URL = $URL.'&return=error2';
                                header("Location: {$URL}");
                                exit();
                            }

                            if ($resultAI->rowCount() != 1) {
                                //Fail 2
                                $URL = $URL.'&return=error2';
                                header("Location: {$URL}");
                                exit();
                            } else {
                                $rowAI = $resultAI->fetch();
                                $AI = str_pad($rowAI['Auto_increment'], 12, '0', STR_PAD_LEFT);

                                //Insert working unit
                                $gibbonPersonIDCreator = $_SESSION[$guid]['gibbonPersonID'];
                                $timestamp = date('Y-m-d H:i:s');
                                $name = $row['name'];
                                $gibbonCourseID = $_POST['gibbonCourseID'];
                                $dateStart = NULL ;
                                if (isset($_POST['dateStart'])) {
                                    $dateStart = dateConvert($guid, $_POST['dateStart']);
                                }
                                if ($_POST['dateStart'] == '') {
                                    $dateStart = null;
                                }
                                $gibbonRubricID = $_POST['gibbonRubricID'];
                                if ($gibbonRubricID == '') {
                                    $gibbonRubricID = null;
                                }
                                $theme = $row['theme'];
                                $centralIdea = $row['centralIdea'];
                                $summativeAssessment = $row['summativeAssessment'];
                                $relatedConcepts = $row['relatedConcepts'];
                                $linesOfInquiry = $row['linesOfInquiry'];
                                $teacherQuestions = $row['teacherQuestions'];
                                $provocation = $row['provocation'];
                                $preAssessment = $row['preAssessment'];
                                $formativeAssessment = $row['formativeAssessment'];
                                $resources = $row['resources'];
                                $action = $row['action'];
                                $environments = $row['environments'];

                                try {
                                    $data = array('ibPYPUnitMasterID' => $ibPYPUnitMasterID, 'gibbonCourseClassID' => $class, 'gibbonPersonIDCreator' => $gibbonPersonIDCreator, 'timestamp' => $timestamp, 'name' => $name, 'gibbonCourseID' => $gibbonCourseID, 'dateStart' => $dateStart, 'gibbonRubricID' => $gibbonRubricID, 'theme' => $theme, 'centralIdea' => $centralIdea, 'summativeAssessment' => $summativeAssessment, 'relatedConcepts' => $relatedConcepts, 'linesOfInquiry' => $linesOfInquiry, 'teacherQuestions' => $teacherQuestions, 'provocation' => $provocation, 'preAssessment' => $preAssessment, 'formativeAssessment' => $formativeAssessment, 'resources' => $resources, 'action' => $action, 'environments' => $environments);
                                    $sql = 'INSERT INTO ibPYPUnitWorking SET ibPYPUnitMasterID=:ibPYPUnitMasterID, gibbonCourseClassID=:gibbonCourseClassID, gibbonPersonIDCreator=:gibbonPersonIDCreator, timestamp=:timestamp, name=:name, gibbonCourseID=:gibbonCourseID, dateStart=:dateStart, gibbonRubricID=:gibbonRubricID, theme=:theme, centralIdea=:centralIdea, summativeAssessment=:summativeAssessment, relatedConcepts=:relatedConcepts, linesOfInquiry=:linesOfInquiry, teacherQuestions=:teacherQuestions, provocation=:provocation, preAssessment=:preAssessment, formativeAssessment=:formativeAssessment, resources=:resources, action=:action, environments=:environments';
                                    $result = $connection2->prepare($sql);
                                    $result->execute($data);
                                } catch (PDOException $e) {
                                    //Fail 2
                                    $URL = $URL.'&return=error2';
                                    header("Location: {$URL}");
                                    exit();
                                }

                                //Insert blocks
                                try {
                                    $dataBlocks = array('ibPYPUnitMasterID' => $ibPYPUnitMasterID);
                                    $sqlBlocks = 'SELECT * FROM ibPYPUnitMasterBlock WHERE ibPYPUnitMasterID=:ibPYPUnitMasterID';
                                    $resultBlocks = $connection2->prepare($sqlBlocks);
                                    $resultBlocks->execute($dataBlocks);
                                } catch (PDOException $e) {
                                    $partialFail = true;
                                }
                                if ($resultBlocks->rowCount() > 0) {
                                    while ($rowBlocks = $resultBlocks->fetch()) {
                                        try {
                                            $dataBlockInsert = array('AI' => $AI, 'ibPYPGlossaryID' => $rowBlocks['ibPYPGlossaryID'], 'gibbonOutcomeID' => $rowBlocks['gibbonOutcomeID'], 'content' => $rowBlocks['content'], 'sequenceNumber' => $rowBlocks['sequenceNumber']);
                                            $sqlBlockInsert = 'INSERT INTO ibPYPUnitWorkingBlock SET ibPYPUnitWorkingID=:AI, ibPYPGlossaryID=:ibPYPGlossaryID, gibbonOutcomeID=:gibbonOutcomeID, content=:content, sequenceNumber=:sequenceNumber';
                                            $resultBlockInsert = $connection2->prepare($sqlBlockInsert);
                                            $resultBlockInsert->execute($dataBlockInsert);
                                        } catch (PDOException $e) {
                                            $partialFail = true;
                                        }
                                    }
                                }
                            }
                        }
                    }

                    //Unlock module table
                    try {
                        $sql = 'UNLOCK TABLES';
                        $result = $connection2->query($sql);
                    } catch (PDOException $e) {
                    }

                    if ($partialFail == true) {
                        //Fail 5
                        $URL = $URL.'&return=error5';
                        header("Location: {$URL}");
                    } else {
                        //Success 0
                        $URLSuccess = $URLSuccess.'&return=success0';
                        $_SESSION[$guid]['ibPYPUnitsTab'] = 0;
                        header("Location: {$URLSuccess}");
                    }
                }
            }
        }
    }
}
