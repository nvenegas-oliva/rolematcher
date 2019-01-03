SELECT  p.potentialid, vtiger_contactscf.contactid,pcf.po_career ,vtiger_contactscf.cf_846,vtiger_contactscf.cf_847
FROM vtiger_potential p
INNER JOIN vtiger_potentialscf pcf ON p.potentialid = pcf.potentialid
LEFT JOIN vtiger_contactdetails ON vtiger_contactdetails.role_id = p.potentialid
LEFT JOIN vtiger_contactscf ON vtiger_contactscf.contactid=vtiger_contactdetails.contactid
INNER JOIN vtiger_crmentity crm ON crm.crmid = p.potentialid
INNER JOIN vtiger_account a ON a.accountid = p.related_to
INNER JOIN vtiger_groups g ON g.groupid = crm.smownerid
WHERE pcf.po_status = 'Open'
AND vtiger_contactscf.cf_846 is not NULL
AND crm.deleted = 0
ORDER BY  pcf.po_career;

SELECT  p.potentialid,vtiger_leadscf.leadid as contactid,pcf.po_career ,vtiger_leadscf.cf_665 as cf_846,vtiger_leadscf.cf_666 as cf_847
FROM vtiger_potential p
INNER JOIN vtiger_potentialscf pcf ON p.potentialid = pcf.potentialid
LEFT JOIN vtiger_leaddetails ON vtiger_leaddetails.role_id = p.potentialid
LEFT JOIN vtiger_leadscf ON vtiger_leadscf.leadid=vtiger_leaddetails.leadid
INNER JOIN vtiger_crmentity crm ON crm.crmid = p.potentialid
INNER JOIN vtiger_account a ON a.accountid = p.related_to
INNER JOIN vtiger_groups g ON g.groupid = crm.smownerid
WHERE pcf.po_status = 'Open'
AND vtiger_contactscf.cf_846 is not NULL
AND crm.deleted = 0
ORDER BY  pcf.po_career;


SELECT  p.potentialid, ccf.contactid, pcf.po_career, ccf.cf_846, ccf.cf_847
FROM vtiger_potential p
INNER JOIN vtiger_potentialscf pcf ON p.potentialid = pcf.potentialid
LEFT JOIN vtiger_contactdetails cd ON cd.role_id = p.potentialid
LEFT JOIN vtiger_contactscf ccf ON ccf.contactid = cd.contactid
INNER JOIN vtiger_crmentity crm ON crm.crmid = p.potentialid
INNER JOIN vtiger_account a ON a.accountid = p.related_to
INNER JOIN vtiger_groups g ON g.groupid = crm.smownerid
WHERE pcf.po_status = 'Open'
AND ccf.cf_846 IS NOT NULL
AND crm.deleted = 0
ORDER BY  pcf.po_career;
