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


SELECT DISTINCT po_career
FROM vtiger_potentialscf;


-- First query
SELECT p.potentialid
FROM vtiger_potential p
INNER JOIN vtiger_potentialscf cf ON cf.potentialid = p.potentialid
INNER JOIN vtiger_contactdetails ON vtiger_contactdetails.role_id = p.potentialid
INNER JOIN vtiger_contactscf ON vtiger_contactscf.contactid=vtiger_contactdetails.contactid
INNER JOIN vtiger_account a ON a.accountid = p.related_to
INNER JOIN vtiger_accountscf acf ON acf.accountid = p.related_to
INNER JOIN vtiger_crmentity e ON e.crmid = p.potentialid
INNER JOIN vtiger_crmentity ae ON ae.crmid = a.accountid
INNER JOIN vtiger_groups g ON g.groupid = e.smownerid
LEFT JOIN vtiger_companycontact cc ON cc.companycontactid = p.company_contact_id
WHERE e.deleted = 0 AND (
    (cf_855 BETWEEN '?' AND '?') OR (cf_881 BETWEEN '?' AND '?') OR (cf_855<='?' AND cf_881>='?')
)
AND `po_career` LIKE "%?%" AND `po_status`= "?";


-- Second query
SELECT *, cc.contact_email, a.website,acf.cf_2021, ae.description from vtiger_potential p
INNER JOIN vtiger_potentialscf pcf ON p.potentialid = pcf.potentialid
LEFT JOIN vtiger_contactdetails ON vtiger_contactdetails.role_id = p.potentialid
LEFT JOIN vtiger_contactscf ON vtiger_contactscf.contactid=vtiger_contactdetails.contactid
INNER JOIN vtiger_crmentity crm ON crm.crmid = p.potentialid
INNER JOIN vtiger_account a ON a.accountid = p.related_to
INNER JOIN vtiger_accountscf acf ON acf.accountid = p.related_to
INNER JOIN vtiger_crmentity ae ON ae.crmid = a.accountid
INNER JOIN vtiger_groups g ON g.groupid = crm.smownerid
LEFT JOIN vtiger_companycontact cc ON cc.companycontactid = p.company_contact_id
WHERE p.potentialid NOT IN ($LIST_OF_FILLED_ROLES)
AND pcf.po_career LIKE '%?%'
AND pcf.po_status = "?"
AND g.groupname = "?"
AND crm.deleted = 0
GROUP BY p.potentialid;

select /* *, cc.contact_email, a.website,acf.cf_2021, ae.description */ p.potentialid from vtiger_potential p
inner join vtiger_potentialscf_testing cf on cf.potentialid = p.potentialid
INNER JOIN vtiger_contactdetails ON vtiger_contactdetails.role_id = p.potentialid
     	inner join vtiger_contactscf on vtiger_contactscf.contactid=vtiger_contactdetails.contactid
		inner join vtiger_account a on a.accountid = p.related_to
		inner join vtiger_accountscf acf on acf.accountid = p.related_to
		inner join vtiger_crmentity e on e.crmid = p.potentialid
		inner join vtiger_crmentity ae on ae.crmid = a.accountid
		inner join vtiger_groups g on g.groupid = e.smownerid
		left join vtiger_companycontact cc on cc.companycontactid = p.company_contact_id
		WHERE e.deleted = 0 ;

DESCRIBE vtiger_account;
SELECT * FROM vtiger_account LIMIT 10;

DESCRIBE vtiger_accountscf;
SELECT * FROM vtiger_accountscf LIMIT 10;
g
SELECT p.potentialid
FROM vtiger_potential p
INNER JOIN vtiger_potentialscf cf ON cf.potentialid = p.potentialid
INNER JOIN vtiger_contactdetails ON vtiger_contactdetails.role_id = p.potentialid
INNER JOIN vtiger_contactscf ON vtiger_contactscf.contactid=vtiger_contactdetails.contactid
INNER JOIN vtiger_account a ON a.accountid = p.related_to
INNER JOIN vtiger_accountscf acf ON acf.accountid = p.related_to
INNER JOIN vtiger_crmentity e ON e.crmid = p.potentialid
INNER JOIN vtiger_crmentity ae ON ae.crmid = a.accountid
INNER JOIN vtiger_groups g ON g.groupid = e.smownerid
LEFT JOIN vtiger_companycontact cc ON cc.companycontactid = p.company_contact_id
WHERE e.deleted = 0 AND ( (cf_855 BETWEEN '2018-06-16' AND '2018-07-28') OR (cf_881 BETWEEN '2018-06-16' AND '2018-07-28') OR (cf_855<='2018-06-16' AND cf_881>='2018-07-28') )
AND `po_career` LIKE "%PR %" AND `po_status`="Open" AND `groupname`="London"
