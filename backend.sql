-- First query
SELECT p.potentialid
FROM vtiger_potential
INNER JOIN vtiger_potentialscf cf on cf.potentialid = p.potentialid
INNER JOIN vtiger_contactdetails ON vtiger_contactdetails.role_id = p.potentialid
INNER JOIN vtiger_contactscf on vtiger_contactscf.contactid=vtiger_contactdetails.contactid
INNER JOIN vtiger_account a on a.accountid = p.related_to
INNER JOIN vtiger_accountscf acf on acf.accountid = p.related_to
INNER JOIN vtiger_crmentity e on e.crmid = p.potentialid
INNER JOIN vtiger_crmentity ae on ae.crmid = a.accountid
INNER JOIN vtiger_groups g on g.groupid = e.smownerid
LEFT JOIN vtiger_companycontact cc on cc.companycontactid = p.company_contact_id
WHERE e.deleted = 0;   s
