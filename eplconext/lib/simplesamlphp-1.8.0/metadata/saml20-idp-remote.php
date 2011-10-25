<?php
/**
 * SAML 2.0 remote IdP metadata for simpleSAMLphp.
 *
 * Remember to remove the IdPs you don't use from this file.
 *
 * See: https://rnd.feide.no/content/idp-remote-metadata-reference
 */


$metadata['https://engine.dev.surfconext.nl/authentication/idp/metadata'] = array (
  'name' => array(
    'en' => 'SURFfederatie',
    'nl' => 'SURFfederatie',
  ),
  'entityid' => 'https://engine.dev.surfconext.nl/authentication/idp/metadata',
  'contacts' => 
  array (
  ),
  'metadata-set' => 'saml20-idp-remote',
  'expire' => 2316002364,
  'SingleSignOnService' => 
  array (
    0 => 
    array (
      'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
      'Location' => 'https://engine.dev.surfconext.nl/authentication/idp/single-sign-on',
    ),
  ),
  'SingleLogoutService' => 
  array (
  ),
  'ArtifactResolutionService' => 
  array (
  ),
  'keys' => 
  array (
    0 => 
    array (
      'encryption' => false,
      'signing' => true,
      'type' => 'X509Certificate',
      'X509Certificate' => 'MIID/jCCAuYCCQCs7BsDR2N8tjANBgkqhkiG9w0BAQUFADCBwDELMAkGA1UEBhMC
TkwxEDAOBgNVBAgTB1V0cmVjaHQxEDAOBgNVBAcTB1V0cmVjaHQxJTAjBgNVBAoT
HFNVUkZuZXQgQlYgLSBUaGUgTmV0aGVybGFuZHMxHzAdBgNVBAsTFkNvbGxhYm9y
YXRpb24gU2VydmljZXMxGDAWBgNVBAMTD1NVUkZjb25leHQtdGVzdDErMCkGCSqG
SIb3DQEJARYcc3VyZmNvbmV4dC1iZWhlZXJAc3VyZm5ldC5ubDAeFw0xMTA2Mjcx
NTM0NDFaFw0yMTA2MjQxNTM0NDFaMIHAMQswCQYDVQQGEwJOTDEQMA4GA1UECBMH
VXRyZWNodDEQMA4GA1UEBxMHVXRyZWNodDElMCMGA1UEChMcU1VSRm5ldCBCViAt
IFRoZSBOZXRoZXJsYW5kczEfMB0GA1UECxMWQ29sbGFib3JhdGlvbiBTZXJ2aWNl
czEYMBYGA1UEAxMPU1VSRmNvbmV4dC10ZXN0MSswKQYJKoZIhvcNAQkBFhxzdXJm
Y29uZXh0LWJlaGVlckBzdXJmbmV0Lm5sMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8A
MIIBCgKCAQEA27T+ALNpoU9PAvRYj2orOXaEVcy1fHSU/rZEakpgNzOFIEL9UB6B
vtWdRbO5FT84yN+x2Qzpu6WLpwU7JFb36aRwPmBmabxUx95DhNQNFGA3ZkHu6DOA
81GiG0Ll9p9S/EV2fmHGJdJjh5BP1v0/y7bJ/2JmvdzH+cFhEhFk0Ex9HNbC0Hmy
9Sn8EXbg3RQO5/2e51wSv4uGALkyGM6lrOG/R1GenoAI8Ys7LNxj3NGkhKRUtpwH
oIViWU5cOy26kG9VOG9bAVk51l0LNayMqyieX9UrCp1akQuP3Ir/ogtbKo2zg63H
o1cc43tHHdLZTHp2TWRRGEgnskvGZLddzwIDAQABMA0GCSqGSIb3DQEBBQUAA4IB
AQB8eUq/0ArBGnPZHNn2o2ip+3i0U4r0swKXjuYL/o2VXqo3eI9j/bCvWw5NOLQV
bk/Whc6dSeYIt1oVW/ND4iQZ7LHZr9814IOE3KLGIMeZgjPsXH15o9W4aLC0npPY
ilw96dfIAq49tOn44jhsRHrdR8z/NFPXE09ehAEb7fOIrxdlf7YDGYx+gXLEnsJo
75E6LwCr/y/MBd13DDJNc1HUViiEz+Mkfo4FEKe/5HgEvDy2XjuE1juDCqJ/07pB
PHBd0DtM7uaxGw+Zt/Fc4uE0NvtlCZqShFUvMmqHL5oENOlfTmBSWJMbBAGs2O/J
QirG2aYcULqXYoCGIPUF49Z6
',
    ),
    1 => 
    array (
      'encryption' => true,
      'signing' => false,
      'type' => 'X509Certificate',
      'X509Certificate' => 'MIID/jCCAuYCCQCs7BsDR2N8tjANBgkqhkiG9w0BAQUFADCBwDELMAkGA1UEBhMC
TkwxEDAOBgNVBAgTB1V0cmVjaHQxEDAOBgNVBAcTB1V0cmVjaHQxJTAjBgNVBAoT
HFNVUkZuZXQgQlYgLSBUaGUgTmV0aGVybGFuZHMxHzAdBgNVBAsTFkNvbGxhYm9y
YXRpb24gU2VydmljZXMxGDAWBgNVBAMTD1NVUkZjb25leHQtdGVzdDErMCkGCSqG
SIb3DQEJARYcc3VyZmNvbmV4dC1iZWhlZXJAc3VyZm5ldC5ubDAeFw0xMTA2Mjcx
NTM0NDFaFw0yMTA2MjQxNTM0NDFaMIHAMQswCQYDVQQGEwJOTDEQMA4GA1UECBMH
VXRyZWNodDEQMA4GA1UEBxMHVXRyZWNodDElMCMGA1UEChMcU1VSRm5ldCBCViAt
IFRoZSBOZXRoZXJsYW5kczEfMB0GA1UECxMWQ29sbGFib3JhdGlvbiBTZXJ2aWNl
czEYMBYGA1UEAxMPU1VSRmNvbmV4dC10ZXN0MSswKQYJKoZIhvcNAQkBFhxzdXJm
Y29uZXh0LWJlaGVlckBzdXJmbmV0Lm5sMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8A
MIIBCgKCAQEA27T+ALNpoU9PAvRYj2orOXaEVcy1fHSU/rZEakpgNzOFIEL9UB6B
vtWdRbO5FT84yN+x2Qzpu6WLpwU7JFb36aRwPmBmabxUx95DhNQNFGA3ZkHu6DOA
81GiG0Ll9p9S/EV2fmHGJdJjh5BP1v0/y7bJ/2JmvdzH+cFhEhFk0Ex9HNbC0Hmy
9Sn8EXbg3RQO5/2e51wSv4uGALkyGM6lrOG/R1GenoAI8Ys7LNxj3NGkhKRUtpwH
oIViWU5cOy26kG9VOG9bAVk51l0LNayMqyieX9UrCp1akQuP3Ir/ogtbKo2zg63H
o1cc43tHHdLZTHp2TWRRGEgnskvGZLddzwIDAQABMA0GCSqGSIb3DQEBBQUAA4IB
AQB8eUq/0ArBGnPZHNn2o2ip+3i0U4r0swKXjuYL/o2VXqo3eI9j/bCvWw5NOLQV
bk/Whc6dSeYIt1oVW/ND4iQZ7LHZr9814IOE3KLGIMeZgjPsXH15o9W4aLC0npPY
ilw96dfIAq49tOn44jhsRHrdR8z/NFPXE09ehAEb7fOIrxdlf7YDGYx+gXLEnsJo
75E6LwCr/y/MBd13DDJNc1HUViiEz+Mkfo4FEKe/5HgEvDy2XjuE1juDCqJ/07pB
PHBd0DtM7uaxGw+Zt/Fc4uE0NvtlCZqShFUvMmqHL5oENOlfTmBSWJMbBAGs2O/J
QirG2aYcULqXYoCGIPUF49Z6
',
    ),
  ),
);

?>
