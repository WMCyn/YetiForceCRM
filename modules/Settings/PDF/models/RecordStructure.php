<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Settings_PDF_RecordStructure_Model extends Vtiger_RecordStructure_Model
{

	const RECORD_STRUCTURE_MODE_DEFAULT = '';
	const RECORD_STRUCTURE_MODE_FILTER = 'Filter';
	const RECORD_STRUCTURE_MODE_EDITTASK = 'EditTask';

	function setPDFModel($pdfModel)
	{
		$this->pdfModel = $pdfModel;
	}

	function getPDFModel()
	{
		return $this->pdfModel;
	}

	/**
	 * Function to get the values in stuctured format
	 * @return <array> - values in structure array('block'=>array(fieldinfo));
	 */
	public function getStructure()
	{
		if (!empty($this->structuredValues)) {
			return $this->structuredValues;
		}

		$recordModel = $this->getPDFModel();
		$recordId = $recordModel->getId();

		$values = array();

		$baseModuleModel = $moduleModel = $this->getModule();
		$blockModelList = $moduleModel->getBlocks();
		foreach ($blockModelList as $blockLabel => $blockModel) {
			$fieldModelList = $blockModel->getFields();
			if (!empty($fieldModelList)) {
				$values[$blockLabel] = array();
				foreach ($fieldModelList as $fieldName => $fieldModel) {
					if ($fieldModel->isViewable()) {
						if (in_array($moduleModel->getName(), array('Calendar', 'Events')) && $fieldName != 'modifiedby' && $fieldModel->getDisplayType() == 3) {
							/* Restricting the following fields(Event module fields) for "Calendar" module
							 * time_start, time_end, eventstatus, activitytype,	visibility, duration_hours,
							 * duration_minutes, reminder_time, recurringtype, notime
							 */
							continue;
						}
						if (!empty($recordId)) {
							//Set the fieldModel with the valuetype for the client side.
							$fieldValueType = $recordModel->getFieldFilterValueType($fieldName);
							$fieldInfo = $fieldModel->getFieldInfo();
							$fieldInfo['workflow_valuetype'] = $fieldValueType;
							$fieldModel->setFieldInfo($fieldInfo);
						}
						// This will be used during editing task like email, sms etc
						$fieldModel->set('workflow_columnname', $fieldName)->set('workflow_columnlabel', vtranslate($fieldModel->get('label'), $moduleModel->getName()));
						// This is used to identify the field belongs to source module of workflow
						$fieldModel->set('workflow_sourcemodule_field', true);
						$values[$blockLabel][$fieldName] = clone $fieldModel;
					}
				}
			}
		}

		//All the reference fields should also be sent
		$fields = $moduleModel->getFieldsByType(array('reference', 'owner', 'multireference'));
		foreach ($fields as $parentFieldName => $field) {
			$type = $field->getFieldDataType();
			$referenceModules = $field->getReferenceList();
			if ($type == 'owner')
				$referenceModules = array('Users');
			foreach ($referenceModules as $refModule) {
				$moduleModel = Vtiger_Module_Model::getInstance($refModule);
				$blockModelList = $moduleModel->getBlocks();
				foreach ($blockModelList as $blockLabel => $blockModel) {
					$fieldModelList = $blockModel->getFields();
					if (!empty($fieldModelList)) {
						foreach ($fieldModelList as $fieldName => $fieldModel) {
							if ($fieldModel->isViewable()) {
								$name = "($parentFieldName : ($refModule) $fieldName)";
								$label = vtranslate($field->get('label'), $baseModuleModel->getName()) . ' : (' . vtranslate($refModule, $refModule) . ') ' . vtranslate($fieldModel->get('label'), $refModule);
								$fieldModel->set('workflow_columnname', $name)->set('workflow_columnlabel', $label);
								if (!empty($recordId)) {
									$fieldValueType = $recordModel->getFieldFilterValueType($name);
									$fieldInfo = $fieldModel->getFieldInfo();
									$fieldInfo['workflow_valuetype'] = $fieldValueType;
									$fieldModel->setFieldInfo($fieldInfo);
								}
								$values[$field->get('label')][$name] = clone $fieldModel;
							}
						}
					}
				}
			}
		}
		$this->structuredValues = $values;
		return ['dupa'];
		return $values;
	}

	public static function getInstanceForPDFModule($pdfModel, $mode)
	{
		$className = Vtiger_Loader::getComponentClassName('Model', $mode . 'RecordStructure', 'Settings:PDF');
		$instance = new $className();
		$instance->setPDFModel($pdfModel);
		$instance->setModule($pdfModel->getModule());
		return $instance;
	}

	/**
	 * Function to get the module
	 * @return <Vtiger_Module_Model>
	 */
	public function getModule($moduleName)
	{
		if ($this->module == null) {
			$className = Vtiger_Loader::getComponentClassName('Model', 'Module', 'Potentials');
			$recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
			$this->module = $recordModel->getModule($moduleName);
		}
		return $this->module;
	}
}
