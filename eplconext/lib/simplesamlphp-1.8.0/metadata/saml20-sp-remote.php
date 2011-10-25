<?php
/**
 * SAML 2.0 remote SP metadata for simpleSAMLphp.
 *
 * See: http://simplesamlphp.org/docs/trunk/simplesamlphp-reference-sp-remote
 */

/*
 * Example simpleSAMLphp SAML 2.0 SP
 */
$metadata['https://saml2sp.example.org'] = array(
	'AssertionConsumerService' => 'https://saml2sp.example.org/simplesaml/module.php/saml/sp/saml2-acs.php/default-sp',
	'SingleLogoutService' => 'https://saml2sp.example.org/simplesaml/module.php/saml/sp/saml2-logout.php/default-sp',
);

$metadata['https://etherpad.conext.surfnetlabs.nl/simplesaml/module.php/saml/sp/metadata.php/default-sp'] = array (
  'AssertionConsumerService' => 'http://eplight.dev/simplesaml/module.php/saml/sp/saml2-acs.php/default-sp',
  'SingleLogoutService' => 'http://eplight.dev/simplesaml/module.php/saml/sp/saml2-logout.php/default-sp',
  'certData' => 'MIIDnzCCAoegAwIBAgIJAIDQXxYxof3mMA0GCSqGSIb3DQEBBQUAMGYxCzAJBgNVBAYTAk5MMRMwEQYDVQQIDApPdmVyaWpzc2VsMQ8wDQYDVQQHDAZad29sbGUxEjAQBgNVBAoMCUNvem1hbm92YTEMMAoGA1UECwwDbGFiMQ8wDQYDVQQDDAZlcGxpdGUwHhcNMTEwOTA2MTU1MzI2WhcNMjEwOTA1MTU1MzI2WjBmMQswCQYDVQQGEwJOTDETMBEGA1UECAwKT3Zlcmlqc3NlbDEPMA0GA1UEBwwGWndvbGxlMRIwEAYDVQQKDAlDb3ptYW5vdmExDDAKBgNVBAsMA2xhYjEPMA0GA1UEAwwGZXBsaXRlMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA1Tbq0j+Q6GSA6Zwb/WjhfFijLGwiZtg8zoGuBJGsVbxBdo30okiSQhlpI1Vwb2X2k1t9o9+tO9FvU/FAbk8CM7mYXAb0d9lTqJ51PaQohUVO5n1coaIHuH1xgBCv7zvnFMt+r08WdBzbjBAgtgupv2GGIWUqAKBo3ZzZxgnNl9utMVnU+GUVcQbd04t/8xE9NOAS+VrTLVneos0C85KDxwWbuqMlDY5RX6EEhf8nglUkyXllXArBXlhlAIEy6qjpcs0l6ieWqU5Ny7Y2JDMOA76raEPVMv1ATWpnerVlLvRdXJcZCXcBfEgDs3HxCHUY5xh4iMmjLxl+nmgMXuGGbQIDAQABo1AwTjAdBgNVHQ4EFgQUcLoEx3JMg0J4U6+Or5v+lYwBXCQwHwYDVR0jBBgwFoAUcLoEx3JMg0J4U6+Or5v+lYwBXCQwDAYDVR0TBAUwAwEB/zANBgkqhkiG9w0BAQUFAAOCAQEAlyUe+2azAh/yDidGUNQaPsFFsWFxhWBpMGzv5yaop8AaiikQV152/xlnV3DN66RYAxqhbFnlGmu62c2imObtOF76iTb+fh7nt4RZCgi9q9Am7lKFrCDbm01Zm/VXTO4oO3uzw4xiJkQRqdeaVVKBUHAlgOzrJb/DOt5F2cnsnQbbIVTVeDWkb+7dd2fVj2C2LE6N4ZtjlPCkx79d6hmg6llH18+wXxxS2Uh9mVwaojzcIg7XwHuu2eg24o9+wIHPuv1/RjlQoCNxklVin+ifkSQhk9ncoltdrs7KEPnJ72Pwtck1ptNpSJ0TkdcKXzVD8QW9p14W05ER4QUoIejEVA==',
);


/*
 * This example shows an example config that works with Google Apps for education.
 * What is important is that you have an attribute in your IdP that maps to the local part of the email address
 * at Google Apps. In example, if your google account is foo.com, and you have a user that has an email john@foo.com, then you
 * must set the simplesaml.nameidattribute to be the name of an attribute that for this user has the value of 'john'.
 */
$metadata['google.com'] = array(
	'AssertionConsumerService' => 'https://www.google.com/a/g.feide.no/acs',
	'NameIDFormat' => 'urn:oasis:names:tc:SAML:2.0:nameid-format:email',
	'simplesaml.nameidattribute' => 'uid',
	'simplesaml.attributes' => FALSE,
);
