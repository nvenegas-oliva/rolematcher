<?php
ini_set('display_errors', 1);
error_reporting(E_ERROR);
chdir('..');
require_once('include/utils/utils.php');
require_once('includes/runtime/Globals.php');
require_once('includes/runtime/LanguageHandler.php');
require_once('includes/runtime/BaseModel.php');
require_once('includes/Loader.php');
require_once('modules/Vtiger/models/Record.php');
require_once('modules/Users/models/Record.php');
include_once('include/Webservices/DescribeObject.php');
include_once('include/Webservices/Create.php');
include_once('include/Webservices/Revise.php');
include_once 'include/Webservices/Retrieve.php';
include_once('include/Webservices/Delete.php');
include_once('include/Webservices/Utils.php');

require_once("vtlib/Vtiger/Module.php");
require_once("vtlib/Vtiger/Block.php");
require_once("vtlib/Vtiger/Field.php");
include_once 'includes/main/WebUI.php';
global $adb, $log;

$esdebug=true;

$txnid = substr(md5(microtime(true)),0,5);

function debugEsLog($var) {

	global $esdebug,$txnid;

	 

		$string = $txnid."\t".date("Y-m-d H:i:s")."\t";

		$string.= (is_array($var) || is_object($var)) ? print_r($var,true) : $var;

		$string.="\n";

		file_put_contents("post/debug2.log",$string,FILE_APPEND);

	

}

/* Config */
$required_fields = array(
	'entity'
);
$valid_entities = array(
	'Contacts',
	'Leads',
	'Accounts',
	'Potentials',
);

/* Processing of the request */
if(empty($_POST)) {
	$request = $_GET;
} else {
	$request = $_POST;
}
$redir = false;
if(isset($request["_redir"]))
	$redir = $request["_redir"];
debugEsLog($request);
//Basic input check
if(empty($request))
	exit('Missing input');
//End Basic input check

//Mandatory field check
$missing = array();;
foreach($required_fields as $k=>$v) {
	if(!isset($request[$v]) || empty($request[$v])) {
		$missing[] = $v;
	}
}
if(!empty($missing)) {
	if(count($missing)==1)
		echo "Missing mandatory field: ".$missing[0]."<br>";
	else
		echo "Missing mandatory fields: ".implode(", ",$missing)."<br>";
	exit();
}
//End Mandatory field check

//Entity type check
$entity = false;
foreach($valid_entities as $k=>$entity_type) {
	if(strtolower($request['entity']) == strtolower($entity_type))
		$entity = $entity_type;
}
if($entity === false)
	exit('Invalid Entity: '.$request['entity'].'. Must be one of '.implode(", ",$valid_entities));
//End Entity type check

//Create the default user
if(!isset($current_user)) {
	$current_user = CRMEntity::getInstance('Users');
	$current_user->retrieveCurrentUserInfoFromFile($current_user->getActiveAdminId());
}
//End Create the default user

//Initiate the borupost object and translate fields
$borupost = new borupost($valid_entities);
$fields = $borupost->translateRequest($entity,$request);
//End Initiate the borupost object and translate fields
$nofields = false; 
if($entity == "Contacts") {
	$sql = "select * from vtiger_contactdetails c
	inner join vtiger_contactscf cf on cf.contactid=c.contactid
	inner join vtiger_contactaddress a on a.contactaddressid=c.contactid
	inner join vtiger_contactsubdetails d on d.contactsubscriptionid=c.contactid
	inner join vtiger_crmentity e on e.crmid=c.contactid
	WHERE e.deleted=0 AND ";
} elseif($entity == "Accounts") {
	$sql = "select * from vtiger_account a
	inner join vtiger_accountscf cf on cf.accountid=a.accountid
	inner join vtiger_crmentity e on e.crmid=a.accountid
	WHERE e.deleted=0 AND ";
} elseif($entity == "Leads") {
	$sql = "select * from vtiger_leaddetails l
	inner join vtiger_leadscf cf on cf.leadid=l.leadid
	inner join vtiger_leadaddress a on a.leadaddressid=l.leadid
	inner join vtiger_leadsubdetails d on d.leadsubscriptionid=l.leadid
	inner join vtiger_crmentity e on e.crmid=l.leadid
	WHERE e.deleted=0 AND ";
} elseif ($entity == "Potentials") {
	if (!empty($request['po_destination'])){
		$fields['groupname'] = $request['po_destination']; 
		unset($fields['po_destination']); 
		
	}
	if (!empty($request['po_career']) && strpos($request['po_career'] , '+') !== false ){
		$fields['po_career'] = str_replace('+', '', $request['po_career']) ;
	}
	
	if(!empty($request['cf_855']) || !empty($request['cf_881'])) {
		$condition = array();
		if( !empty($request['cf_855']) && !empty($request['cf_881']) ) {
			$condition[] = sprintf(" AND ( (cf_855 BETWEEN '%s' AND '%s') OR (cf_881 BETWEEN '%s' AND '%s') OR (cf_855<='%s' AND cf_881>='%s') ) ", $request['cf_855'], $request['cf_881'], $request['cf_855'], $request['cf_881'], $request['cf_855'], $request['cf_881'] );
		}
		 
		
		$sql = "select /* *, cc.contact_email, a.website,acf.cf_2021, ae.description */ p.potentialid from vtiger_potential p
		inner join vtiger_potentialscf cf on cf.potentialid = p.potentialid
		INNER JOIN vtiger_contactdetails ON vtiger_contactdetails.role_id = p.potentialid 		
        	inner join vtiger_contactscf on vtiger_contactscf.contactid=vtiger_contactdetails.contactid 
		inner join vtiger_account a on a.accountid = p.related_to
		inner join vtiger_accountscf acf on acf.accountid = p.related_to
		inner join vtiger_crmentity e on e.crmid = p.potentialid
		inner join vtiger_crmentity ae on ae.crmid = a.accountid
		inner join vtiger_groups g on g.groupid = e.smownerid
		left join vtiger_companycontact cc on cc.companycontactid = p.company_contact_id
		WHERE e.deleted = 0 ".implode(' ', $condition) ;
		if (!empty($fields)) $sql .= " AND ";  		
		$nofields = true;
	}
	else {
		$sql = "select *, cc.contact_email, a.website,acf.cf_2021, ae.description from vtiger_potential p
		inner join vtiger_potentialscf cf on cf.potentialid = p.potentialid
		inner join vtiger_account a on a.accountid = p.related_to
		inner join vtiger_accountscf acf on acf.accountid = p.related_to
		inner join vtiger_crmentity e on e.crmid = p.potentialid
		inner join vtiger_crmentity ae on ae.crmid = a.accountid
		inner join vtiger_groups g on g.groupid = e.smownerid
		left join vtiger_companycontact cc on cc.companycontactid = p.company_contact_id
		WHERE e.deleted = 0 AND ";
	}
}
$fetch_all = isset($request["fetch_all"]) && $request["fetch_all"];
if(isset($request["record"]))
	$fields["crmid"] = $request["record"];
if(!empty($fields) || $nofields) {
	$values = array();
	// task_id=38835
	$array_role_id = array();
	if ($entity == "Contacts" && $request['role_id'] > 0) {

		$fetch_all = true; 

		$role_id = $request['role_id'];

		// entity=Contacts&role_id=1439247

		// search for role label that matches current role_id

			

		// search for all role_id's that match the label

		$sql_role = sprintf("select role_id from vtiger_contactdetails

				WHERE tp_3 in (select tp_3 from vtiger_contactdetails

				WHERE role_id= %d )", $role_id );

	

		$result_role = $adb->query($sql_role );

		

		// Build array of role_id's

		while ($result_role && $row = $adb->fetch_row($result_role)) {

			$array_role_id[] = $row["role_id"];

		}

	} 
	
	foreach($fields as $k=>$v) {
		if ($entity === "Potentials") {
		    if ($k === "po_career") {
		        $conditions[] = "`$k` like ?";
		        $values[] = "%$v%";
		        continue;
		    } else if ($k === "po_destination") {
		        $conditions[] = "`groupname`=?";
		    } else {
		        $conditions[] = "`$k`=?";
		    }
		} else {
			if ($entity == "Contacts" && $k=='role_id' && !empty($array_role_id)) continue; 
			else $conditions[] = "`$k`=?";
		}
		if ($entity == "Contacts" && $k=='role_id' && !empty($array_role_id)) continue;

		else $values[] = $v;
	}
	
	// task_id=38835

	if ($entity == "Contacts" && !empty($array_role_id)){ 
		// fix problem with AND at the end of query if $conditions is not empty 
		if (empty($conditions)) $sql.= " role_id in (".implode(',', $array_role_id).") " ;

		else $sql.= " role_id in (".implode(',', $array_role_id).") AND " ;

	}
	
	$sql.=implode(" AND ",$conditions); 
	 
	
	if ($entity !== "Potentials" && !$fetch_all) {
		$sql.=" LIMIT 1";
	}
	
	//echo $sql; echo "\n\n"; var_dump($values); exit; 
	debugEsLog($sql);
	debugEsLog($values);
	$result = $adb->pquery($sql,$values);
	if ($fetch_all && (!empty($request['cf_855']) || !empty($request['cf_881'])) ) {
		while ($result && $row = $adb->fetch_row($result)) {
			$occupied_roles[] = $row['potentialid'];
		}
		$occupied_roles_str = '';
		foreach($occupied_roles as $occupied_role){
			$occupied_roles_str .= "$occupied_role,";
		}
		if (substr($occupied_roles_str, -1, 1) == ','){
		  $occupied_roles_str = substr($occupied_roles_str, 0, -1);
		}
		$unoccupied_roles_query = "SELECT *, cc.contact_email, a.website,acf.cf_2021, ae.description from vtiger_potential p
						inner join vtiger_potentialscf pcf ON p.potentialid = pcf.potentialid 
						LEFT JOIN vtiger_contactdetails ON vtiger_contactdetails.role_id = p.potentialid 
						LEFT JOIN vtiger_contactscf ON vtiger_contactscf.contactid=vtiger_contactdetails.contactid 
						INNER JOIN vtiger_crmentity crm ON crm.crmid = p.potentialid
						INNER JOIN vtiger_account a ON a.accountid = p.related_to
						INNER JOIN vtiger_accountscf acf ON acf.accountid = p.related_to
						INNER JOIN vtiger_crmentity ae ON ae.crmid = a.accountid 
						INNER JOIN vtiger_groups g ON g.groupid = crm.smownerid 
						LEFT JOIN vtiger_companycontact cc ON cc.companycontactid = p.company_contact_id 
						WHERE 
						p.potentialid NOT IN ($occupied_roles_str)
						AND pcf.po_career LIKE ? AND pcf.po_status = ?
						AND g.groupname = ? 
						AND crm.deleted = 0
						GROUP BY p.potentialid
						";
		$res = $adb->pquery($unoccupied_roles_query,$values);
		while ($res && $row = $adb->fetch_row($res)){
			$json[] = $row; 
		}
		//echo $unoccupied_roles_query; var_dump($values); exit;
		echo json_encode($json);
		exit();
	}else if ($fetch_all && (empty($request['cf_855']) || empty($request['cf_881'])) ){
		while ($result && $row = $adb->fetch_row($result)) {
                        $json[] = $row;
                }
                echo json_encode($json);
                exit();
	}	
	$recordid=false;
	while($result && $row=$adb->fetch_row($result)) {
		$recordid = $row["crmid"];
	}
	if($recordid!==false) {
		try {
			$wsid = vtws_getWebserviceEntityId($entity, $recordid);
			$record = vtws_retrieve($wsid, $current_user);
			echo json_encode($record);
		} catch (WebServiceException $ex) {
			echo "ERROR";
		}
	} else {
		echo "NORECORD";
	}
} else {
	echo "NOFIELDS";
}


if($redir !== false)
	header("Location: $redir");
else
	echo $output;
exit();
//End Create/Edit the record

/* Defined class to handle the request processing */
class borupost {
	var $debug = true;
	var $tabids;
	var $modules;
	var $fields;
	function  __construct($modules=array()) {
		$this->listModules($modules);
	}

	function editEntity($entity_type,$recordid,$fields) {
		global $current_user;
		$obj = Vtiger_Record_Model::getInstanceById($recordid, $entity_type);
		$obj->set('mode', 'edit');
		$obj->set('id', $recordid);
		foreach($fields as $field=>$value) {
			$obj->set($field,$value);
		}
		$obj->save();
		return $obj->get('id');
	}
	function createEntity($entity_type,$fields) {
		global $current_user;
		$obj = Vtiger_Record_Model::getCleanInstance($entity_type);
		$obj->set('mode', '');
		$assignedto = false;
		foreach($fields as $field=>$value) {
			if($field == "assigned_user_id")
				$assignedto = true;
			$obj->set($field,$value);
		}
		if(!$assignedto) {
			$obj->set("assigned_user_id",1);
		}
		$obj->save();
		return $obj->get('id');
	}

	function translateRequest($module,$input_fields) {
		$output_fields = array();
		$module_fields = $this->getFields($module);
		foreach($input_fields as $field=>$value) {
			foreach($module_fields as $fn=>$fm) {
				$uitype = $fm->get('uitype');
				$fieldname = $fm->getName();
				$fieldcol = $fm->get('column');
				$field_options = array(strtolower($fieldname),strtolower($fieldcol),strtolower(vtranslate($fm->get('label'),$module)),str_replace(" ","_",strtolower(vtranslate($fm->get('label'),$module))));
				if(in_array(strtolower(urldecode($field)),$field_options)) {
					$output_fields[$fieldname] = $value;
					break;
				}
			}
		}
		return $output_fields;
	}
	function getMandatoryFields($entity) {
		$fields = array();
		$desc = $this->describeRecord($entity);
		foreach($desc["fields"] as $dfieldid=>$dfield) {
			$d_fieldname = $dfield["name"];
			$d_mandatory = $dfield["mandatory"];
			if($d_mandatory === true) {
				$fields[] = $d_fieldname;
			}
		}
		return $fields;
	}
	function getFields($moduleIdOrName) {
		$tabid = $this->getTabid($moduleIdOrName);
		if(!isset($this->fields[$tabid])) $this->fields[$tabid] = $this->modules[$tabid]->getFields();
		return $this->fields[$tabid];
	}

	function getTabid($input) {
		if(!isset($this->tabids[$input])) {
			if(is_numeric($input)) {
				$tabid = $input;
			} else {
				$tabid = Vtiger_Functions::getModuleId($input);
				if(empty($tabid) || $tabid === false)
					throw new Exception('Unkown Module Name');
			}
			$this->tabids[$input] = $tabid;
		}
		return $this->tabids[$input];
	}

	function listModules($input=array()) {
		global $adb;


		if(empty($input)) {
			$sql = "SELECT * FROM vtiger_tab WHERE isentitytype=1 AND presence <> 1 ORDER BY `tablabel` ASC";
			$result = $adb->query($sql);
		} else {
			$string = "";
			foreach($input as $k=>$v) {
				$string.=",?";
			}
			$string = trim($string,",");
			$sql = "SELECT * FROM vtiger_tab WHERE isentitytype=1 AND presence <> 1 AND `name` IN ($string) ORDER BY `tablabel` ASC";
			$result = $adb->pquery($sql,$input);
		}

		$this->modules = array();
		while($result && $row=$adb->fetch_row($result)) {
			$this->modules[$row["tabid"]] = Vtiger_Module_Model::getInstanceFromArray($row);
		}
		return $this->modules;
	}
	function describeRecord($module,$cache=true) {
		//echo "describe_cache count: ".count($this->describe_cache)."\n";
		if($cache)
			if(isset($this->describe_cache[$module])) { return $this->describe_cache[$module]; }
		global $current_user;
		try {
			$record = vtws_describe($module,$current_user);
			$this->describe_cache[$module] = $record;
			return $record;
		} catch (WebServiceException $ex) {
			echo $ex->getMessage()."\n";
			exit();
		}
		return false;
	}
}
