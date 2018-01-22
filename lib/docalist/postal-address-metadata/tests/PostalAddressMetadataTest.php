<?php
/**
 * This file is part of Docalist Postal Address Metadata.
 *
 * Copyright (C) 2012-2018 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */
namespace Docalist\PostalAddressMetadata\Tests;

use PHPUnit_Framework_TestCase;
use Docalist\PostalAddressMetadata\PostalAddressMetadata;

class PostalAddressMetadataTest extends PHPUnit_Framework_TestCase
{
    /**
     * Vérifie que le fichier contenant les meta-données est correct.
     */
    public function testMetadata()
    {
        $data = require PostalAddressMetadata::METADATA_PATH;

        // Vérifie qu'on a un tableau
        $this->assertTrue(is_array($data));

        // Vérifie que l'entrée 'ZZ' existe (valeurs par défaut)
        $this->assertTrue(isset($data['ZZ']) && is_array($data['ZZ']));
        $default = $data['ZZ'];

        // Vérifie chacune des entrées
        foreach($data as $code => $data) {
            // Les codes pays doivent contenir exactement deux caractères en maju
            $this->assertTrue(1 === preg_match('~[A-Z]{2}~', $code), 'Invalid country code "' . $code . '"');

            // La valeur associée doit être un tableau
            $this->assertTrue(is_array($data));

            // Combine l'entrée avec les valeurs par défaut (comme le fait getMetadataFor)
            $data += $default;

            // On n'a que des clés autorisées
            foreach(array_keys($data) as $key) {
                $this->assertOneOf(
                    [
                        'fmt', 'locality_name_type', 'require', 'state_name_type', 'sublocality_name_type',
                        'upper', 'zip', 'zip_name_type', 'zipex'
                    ],
                    $key,
                    'Invalid key "' . $key . '" for country "' . $code . '"'
                );
            }

            // La clé 'fmt' doit exister et doit être valide
            $this->assertTrue(
                ! empty($data['fmt']) && is_string($data['fmt']),
                'No "fmt" key for country "' . $code . '"'
            );
            $this->assertValidFormat($data['fmt'], 'Invalid "fmt" for country "' . $code . '"');

            // La clé 'locality_name_type' doit exister et doit être valide
            $this->assertTrue(
                ! empty($data['locality_name_type']) && is_string($data['locality_name_type']),
                'No "locality_name_type" key for country "' . $code . '"'
            );
            $this->assertOneOf(
                ['city', 'district', 'post_town', 'suburb'],
                $data['locality_name_type'],
                'Bad locality_name_type "' . $data['locality_name_type'] . '" for country "' . $code . '"'
            );

            // La clé 'require' doit exister et doit être valide
            $this->assertTrue(
                ! empty($data['require']) && is_string($data['require']),
                'No "require" key for country "' . $code . '"'
            );
            $this->assertValidCodes($data['require'], 'Bad "require" key for country "' . $code . '"');

            // La clé 'state_name_type' doit exister et doit être valide
            $this->assertTrue(
                ! empty($data['state_name_type']) && is_string($data['state_name_type']),
                'No "state_name_type" key for country "' . $code . '"'
            );
            $this->assertOneOf(
                [
                    'area', 'county', 'department', 'district', 'do_si', 'emirate', 'island',
                    'oblast', 'parish', 'prefecture', 'province', 'state'
                ],
                $data['state_name_type'],
                'Bad state_name_type "' . $data['state_name_type'] . '" for country "' . $code . '"'
            );

            // La clé 'sublocality_name_type' doit exister et doit être valide
            $this->assertTrue(
                ! empty($data['sublocality_name_type']) && is_string($data['sublocality_name_type']),
                'No "sublocality_name_type" key for country "' . $code . '"'
            );
            $this->assertOneOf(
                ['district', 'neighborhood', 'suburb', 'townland', 'village_township'],
                $data['sublocality_name_type'],
                'Bad sublocality_name_type "' . $data['sublocality_name_type'] . '" for country "' . $code . '"'
            );

            // La clé 'upper' doit exister (éventuellement vide exemple 'CH') et doit être valide
            $this->assertTrue(
                isset($data['upper']) && is_string($data['upper']),
                'No "upper" key for country "' . $code . '"'
            );
            $this->assertValidCodes($data['upper'], 'Bad "upper" key for country "' . $code . '"');

            // Si la clé 'zip' existe, elle doit contenir une regexp valide
            if (isset($data['zip'])) {
                $this->assertTrue(
                    !empty($data['zip'] && is_string($data['zip'])),
                    'Bad "zip" key for country "' . $code . '"'
                );
                $this->assertTrue(
                    false !== @preg_match('~' . $data['zip'] . '~', null),
                    'Invalid regex in "zip" key for country "' . $code . '"'
                );
            }

            // La clé 'zip_name_type' doit exister et doit être valide
            $this->assertTrue(
                ! empty($data['zip_name_type']) && is_string($data['zip_name_type']),
                'No "zip_name_type" key for country "' . $code . '"'
            );
            $this->assertOneOf(
                ['eircode', 'pin', 'postal', 'zip'],
                $data['zip_name_type'],
                'Bad zip_name_type "' . $data['zip_name_type'] . '" for country "' . $code . '"'
            );

            // Si la clé 'zipex' existe, elle doit contenir des codes postaux valides
            if (isset($data['zipex'])) {
                $this->assertTrue(
                    !empty($data['zipex'] && is_string($data['zipex'])),
                    'Bad "zipex" key for country "' . $code . '"'
                );
                foreach (explode(',', $data['zipex']) as $zip) {
                    $this->assertTrue(
                        1 === preg_match('~^' . $data['zip'] . '$~', $zip),
                        'Invalid zipex "' . $zip . '" for country "' . $code . '"'
                    );
                }
            }
        }
    }

    /**
     * Vérifie que le format passé en paramètre est correct et qu'ils ne contient pas de %x invalides.
     *
     * @param string $code      Code pays
     * @param string $format    Format
     */
    protected function assertValidFormat($format, $message = '')
    {
        // Supprime toutes les séquences valides
        $check = preg_replace('~%[NOADCSZX]~', '', $format, -1, $count);

        // Vérifie qu'on a au moins trois champs (code 'MO' : on a uniquement '%A', '%N' et '%O')
        $this->assertTrue($count > 2, $message);

        // Supprime tous les retours à la ligne
        $check = preg_replace('~%n~', '', $check, -1, $count);

        // Vérifie qu'on n'a pas plus de 5 retorus à la ligne (i.e. 6/7 lignes d'adresses maxi)
        $this->assertTrue($count < 6, $message);

        // Il reste quelques caractères littéraux, on ne doit plus avoir de signes "%"
        $this->assertTrue(strlen($check) < 11, $message);
        $this->assertFalse(strpos($check, '%'), $message);
    }

    /**
     * Vérifie qu'une valeur est l'une des valeurs autorisées
     *
     * @param array $expected Valeurs autorisées.
     * @param mixed $value Valeur à tester.
     *
     * @param string $message Message à afficher en cas d'erreur.
     */
    protected function assertOneOf(array $expected, $value, $message = '')
    {
        $this->assertTrue(in_array($value, $expected), $message);
    }

    /**
     * Vérifie que les codes de champs présents dans la valeur passée en paramètre sont valides.
     *
     * @param string $value Valeur à tester.
     *
     * @param string $message Message à afficher en cas d'erreur.
     */
    protected function assertValidCodes($value, $message = '')
    {
        $this->assertTrue(empty(trim($value, 'NOADCSZX')), $message);
    }

    /**
     * Teste le constructeur.
     */
    public function testNew()
    {
        $format = new PostalAddressMetadata();
        $this->assertSame('', $format->getCountry());

        $format = new PostalAddressMetadata('');
        $this->assertSame('', $format->getCountry());

        $format = new PostalAddressMetadata('FR');
        $this->assertSame('FR', $format->getCountry());
    }

    /**
     * Teste le constructeur avec un code pays inexistant.
     *
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage No metadata for entry "AB"
     */
    public function testNewInvalidCountry()
    {
        new PostalAddressMetadata('AB');
    }

    /**
     * Teste le constructeur avec un code pays en minuscule.
     *
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage No metadata for entry "fr"
     */
    public function testNewLowercaseCountry()
    {
        new PostalAddressMetadata('fr');
    }

    /**
     * Teste la fonction getCountries.
     */
    public function testGetAvailableCountries()
    {
        $countries = PostalAddressMetadata::getAvailableCountries();
        $this->assertSame(248, count($countries));
        $this->assertFalse(isset($countries['ZZ']));
    }

    /**
     * Teste les méthodes getCountry() et setCountry().
     */
    public function testGetSetCountry()
    {
        $format = new PostalAddressMetadata();
        $this->assertSame('', $format->getCountry());

        $format->setCountry('US');
        $this->assertSame('US', $format->getCountry());

        $format->setCountry('');
        $this->assertSame('', $format->getCountry());
    }

    /**
     * Teste la méthode getFields.
     */
    public function testGetUsedFields()
    {
        $format = new PostalAddressMetadata();
        $this->assertSame(
            [
                'organization',
                'recipient',
                'address',
                'subLocality',
                'postalCode',
                'locality',
                'sortingCode',
                'administrativeArea',
                'country'
            ],
            $format->getUsedFields()
        );

        $format->setCountry('FR');
        $this->assertSame(
            [
                'organization',
                'recipient',
                'address',
                'postalCode',
                'locality',
                'sortingCode',
                'country'
            ],
            $format->getUsedFields()
        );

        $format->setCountry('BR');
        $this->assertSame(
            [
                'organization',
                'recipient',
                'address',
                'subLocality',
                'locality',
                'administrativeArea',
                'postalCode',
                'country'
            ],
            $format->getUsedFields()
        );

        $format->setCountry('KR');
        $this->assertSame(
            [
                'administrativeArea',
                'locality',
                'subLocality',
                'address',
                'organization',
                'recipient',
                'postalCode',
                'country'
            ],
            $format->getUsedFields()
        );

        $format->setCountry('GG');
        $this->assertSame(
            [
                'recipient',
                'organization',
                'address',
                'locality',
                'postalCode',
                'country',
            ],
            $format->getUsedFields()
        );
    }

    /**
     * Teste la méthode getRequiredFields.
     */
    public function testGetRequiredFields()
    {
        $format = new PostalAddressMetadata();
        $this->assertSame(
            [
                'address',
                'locality',
                'country'
            ],
            $format->getRequiredFields()
        );

        $format->setCountry('FR');
        $this->assertSame(
            [
                'address',
                'locality',
                'postalCode',
                'country',
            ],
            $format->getRequiredFields()
        );
    }

    /**
     * Teste la méthode getPostalCodePattern.
     */
    public function testGetPostalCodePattern()
    {
        $format = new PostalAddressMetadata('AE');
        $this->assertSame('', $format->getPostalCodePattern());

        $format->setCountry('FR');
        $this->assertSame('\d{2} ?\d{3}', $format->getPostalCodePattern());
    }

    /**
     * Teste la méthode getUppercaseFields.
     */
    public function testGetUppercaseFields()
    {
        $format = new PostalAddressMetadata();
        $this->assertSame(['locality'], $format->getUppercaseFields());

        $format->setCountry('CH');
        $this->assertSame([], $format->getUppercaseFields());

        $format->setCountry('FR');
        $this->assertSame(['locality', 'sortingCode'], $format->getUppercaseFields());
    }

    /**
     * Teste la méthode getAdministrativeAreaType().
     */
    public function testGetAdministrativeAreaType()
    {
        $format = new PostalAddressMetadata();

        $format->setCountry('HK');
        $this->assertSame('area', $format->getAdministrativeAreaType());

        $format->setCountry('IE');
        $this->assertSame('county', $format->getAdministrativeAreaType());

        $format->setCountry('CO');
        $this->assertSame('department', $format->getAdministrativeAreaType());

        $format->setCountry('NR');
        $this->assertSame('district', $format->getAdministrativeAreaType());

        $format->setCountry('KR');
        $this->assertSame('do_si', $format->getAdministrativeAreaType());

        $format->setCountry('AE');
        $this->assertSame('emirate', $format->getAdministrativeAreaType());

        $format->setCountry('BS');
        $this->assertSame('island', $format->getAdministrativeAreaType());

        $format->setCountry('RU');
        $this->assertSame('oblast', $format->getAdministrativeAreaType());

        $format->setCountry('BB');
        $this->assertSame('parish', $format->getAdministrativeAreaType());

        $format->setCountry('JP');
        $this->assertSame('prefecture', $format->getAdministrativeAreaType());

        $format->setCountry('CA');
        $this->assertSame('province', $format->getAdministrativeAreaType());

        $format->setCountry('US');
        $this->assertSame('state', $format->getAdministrativeAreaType());
    }

    /**
     * Teste la méthode getLocalityType().
     */
    public function testGetLocalityType()
    {
        $format = new PostalAddressMetadata();

        $format->setCountry('FR');
        $this->assertSame('city', $format->getLocalityType());

        $format->setCountry('HK');
        $this->assertSame('district', $format->getLocalityType());

        $format->setCountry('GB');
        $this->assertSame('post_town', $format->getLocalityType());

        $format->setCountry('AU');
        $this->assertSame('suburb', $format->getLocalityType());
    }

    /**
     * Teste la méthode getSubLocalityType().
     */
    public function testGetSubLocalityType()
    {
        $format = new PostalAddressMetadata();

        $format->setCountry('CN');
        $this->assertSame('district', $format->getSubLocalityType());

        $format->setCountry('BR');
        $this->assertSame('neighborhood', $format->getSubLocalityType());

        $format->setCountry('CL');
        $this->assertSame('suburb', $format->getSubLocalityType());

        $format->setCountry('IE');
        $this->assertSame('townland', $format->getSubLocalityType());

        $format->setCountry('MY');
        $this->assertSame('village_township', $format->getSubLocalityType());
    }

    /**
     * Teste la méthode getPostalCodeType().
     */
    public function testGetPostalCodeType()
    {
        $format = new PostalAddressMetadata();

        $format->setCountry('IE');
        $this->assertSame('eircode', $format->getPostalCodeType());

        $format->setCountry('FR');
        $this->assertSame('postal', $format->getPostalCodeType());

        $format->setCountry('US');
        $this->assertSame('zip', $format->getPostalCodeType());

        $format->setCountry('IN');
        $this->assertSame('pin', $format->getPostalCodeType());
    }


    public function testGetAddressStructure()
    {
        $format = new PostalAddressMetadata();

        $format->setCountry('FR');
        $this->assertSame([
            ['organization'],
            ['recipient'],
            ['address'],
            ['postalCode', 'locality', 'sortingCode'],
            ['country'],
        ], $format->getAddressStructure());

        $format->setCountry('BR');
        $this->assertSame([
            ['organization'],
            ['recipient'],
            ['address'],
            ['subLocality'],
            ['locality', 'administrativeArea'],
            ['postalCode'],
            ['country'],
        ], $format->getAddressStructure());

        $format->setCountry('KR');
        $this->assertSame([
            ['administrativeArea', 'locality', 'subLocality'],
            ['address'],
            ['organization'],
            ['recipient'],
            ['postalCode'],
            ['country'],
        ], $format->getAddressStructure());

        $format->setCountry('GG'); // Contient le littéral 'GUERNSEY' qui ne doit pas être retourné
        $this->assertSame([
            ['recipient'],
            ['organization'],
            ['address'],
            ['locality'],
            ['postalCode'],
            ['country'],
        ], $format->getAddressStructure());

        $format->setCountry('GS'); // Contient une ligne vide qui ne doit pas être retournée
        $this->assertSame([
            ['recipient'],
            ['organization'],
            ['address'],
            ['locality'],
            ['postalCode'],
            ['country'],
        ], $format->getAddressStructure());
    }

    public function testFormat()
    {
        $address = [
            'recipient' => 'recipient',
            'organization' => 'organization',
            'address' => "line 1\nline 2",
            'subLocality' => 'sublocality',
            'locality'  => 'locality',
            'administrativeArea' => 'area',
            'postalCode' => 'postal code',
            'sortingCode' => 'sorting code',
            'country' => '',
        ];

        $format = new PostalAddressMetadata();
        $this->assertSame(
            "organization\nrecipient\nline 1\nline 2\nsublocality\npostal code locality sorting code\narea",
            $format->format($address, ['uppercase' => false])
        );

        $this->assertSame(
            "organization\nrecipient\nline 1\nline 2\nsublocality\npostal code LOCALITY sorting code\narea",
            $format->format($address, ['uppercase' => true])
        );
    }

    public function testFormatFR()
    {
        $address = [
            'recipient' => 'D. Ménard',
            'organization' => 'Docalist',
            'address' => "18 rue des Épis\nLa Pierre Blanche",
            'locality'  => 'La Forêt-Fouesnant',
            'postalCode' => '29940',
            'sortingCode' => 'Cedex',
            'country' => 'fr',
        ];

        $format = new PostalAddressMetadata();
        $this->assertSame(
            "Docalist\nD. Ménard\n18 rue des Épis\nLa Pierre Blanche\n29940 LA FORET-FOUESNANT CEDEX\nFRANCE",
            $format->format($address, ['uppercase' => true])
        );

        $format->setCountry('FR');
        $this->assertSame(
            "Docalist\nD. Ménard\n18 rue des Épis\nLa Pierre Blanche\n29940 LA FORET-FOUESNANT CEDEX",
            $format->format($address, ['uppercase' => true])
        );
        $this->assertSame(
            "Docalist\nD. Ménard\n18 rue des Épis\nLa Pierre Blanche\n29940 La Forêt-Fouesnant Cedex",
            $format->format($address, ['uppercase' => false])
        );
        $this->assertSame(
            "Docalist, D. Ménard, 18 rue des Épis, La Pierre Blanche, 29940 La Forêt-Fouesnant Cedex",
            $format->format($address, ['uppercase' => false, 'separator' => ', '])
        );

        $this->assertSame(
            '<span class="organization">Docalist</span><br />' .
            '<span class="recipient">D. Ménard</span><br />' .
            '<span class="address">18 rue des Épis<br />La Pierre Blanche</span><br />' .
            '<span class="postalCode">29940</span> ' .
            '<span class="locality">La Forêt-Fouesnant</span> ' .
            '<span class="sortingCode">Cedex</span>',
            $format->format($address, ['html' => true, 'uppercase' => false])
        );

        $this->assertSame(
            '<span class="organization">Docalist</span><br />' .
            '<span class="recipient">D. Ménard</span><br />' .
            '<span class="address">18 rue des Épis<br />La Pierre Blanche</span><br />' .
            '<span class="postalCode">29940</span> ' .
            '<span class="locality">LA FORET-FOUESNANT</span> ' .
            '<span class="sortingCode">CEDEX</span>',
            $format->format($address, ['html' => true, 'uppercase' => true])
        );
    }

    /**
     * Test from : commerceguys/addressing
     */
    public function testFormatAD()
    {
        $address = [
            'country' => 'AD', // Andorre
            'locality'  => "Parròquia d'Andorra la Vella",
            'postalCode' => 'AD500',
            'address' => 'C. Prat de la Creu, 62-64',
        ];

        $format = new PostalAddressMetadata();
        $this->assertSame(
            "C. Prat de la Creu, 62-64\nAD500 PARROQUIA D'ANDORRA LA VELLA\nANDORRE",
            $format->format($address, ['uppercase' => true])
        );

        $format->setCountry('AD');
        $this->assertSame(
            "C. Prat de la Creu, 62-64\nAD500 PARROQUIA D'ANDORRA LA VELLA",
            $format->format($address, ['uppercase' => true])
        );
    }

    /**
     * Test from : commerceguys/addressing
     */
    public function testFormatSV()
    {
        $address = [
            'country' => 'SV', // El Salvador
            'administrativeArea' => 'Ahuachapán',
            'locality'  => 'Ahuachapán',
            'address' => 'Some Street 12',
        ];

        $format = new PostalAddressMetadata();
        $this->assertSame(
            "Some Street 12\nAhuachapán\nAhuachapán\nEl Salvador",
            $format->format($address, ['uppercase' => false])
        );

        $address['postalCode'] = 'CP 2101';
        $this->assertSame(
            "Some Street 12\nCP 2101-Ahuachapán\nAhuachapán\nEl Salvador",
            $format->format($address, ['uppercase' => false])
        );
    }
}
