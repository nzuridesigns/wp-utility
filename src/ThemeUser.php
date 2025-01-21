<?php

namespace Jcodify\CarRentalTheme\Wordpress;

use Jcodify\CarRentalTheme\Wordpress\ORM\Enums\RentalStatus;
use Jcodify\CarRentalTheme\Wordpress\ORM\Helpers\CB;
use Jcodify\CarRentalTheme\Wordpress\ORM\Models\Extended\ExtendedRentalsMetaModel;
use WP_User;

class ThemeUser
{
    public WP_User $user;

    public function __construct(int $id = null)
    {
        if ($id) {
            $this->user = get_userdata($id);
            return;
        }
        $this->user = wp_get_current_user();
    }

    public function getFirstName(): string
    {
        if (!$this->user->first_name) {
            throw new \Exception('User first name not found');
        }
        return ucfirst($this->user->first_name);
    }

    public function getLastName(): string
    {
        if (!$this->user->last_name) {
            throw new \Exception('User last name not found');
        }
        return ucfirst($this->user->last_name);
    }

    public function getFullName(): string
    {
        return "{$this->getFirstName()} {$this->getLastName()}";
    }

    public function getEmail(): string
    {
        if (!$this->user->user_email) {
            throw new \Exception('User email not found');
        }
        return $this->user->user_email;
    }

    public function getId(): int
    {
        return $this->user->ID;
    }

    public function getAddress(): string
    {
        $address = get_user_meta($this->user->ID, 'address', true);
        if (!$address) {
            throw new \Exception('User address not found');
        }
        return $address;
    }
    // apartment_suite_etc,  city, state_province, country, postal_code, rent_car, licence

    public function getApartment(): string
    {
        $apartment = get_user_meta($this->user->ID, 'apartment_suite_etc', true);
        if (!$apartment) {
            throw new \Exception('User Apartment not found');
        }
        return $apartment;
    }
    public function getCity(): string
    {
        $city = get_user_meta($this->user->ID, 'city', true);
        if (!$city) {
            throw new \Exception('User City not found');
        }
        return $city;
    }
    public function getStateProvince(): string
    {
        $stateProvince = get_user_meta($this->user->ID, 'state_province', true);
        if (!$stateProvince) {
            throw new \Exception('User State/Province not found');
        }
        return $stateProvince;
    }

    public function getCountryCode(): string
    {
        $country = get_user_meta($this->user->ID, 'country', true);
        if (!$country) {
            throw new \Exception('User Country Code not found');
        }
        return $country;
    }

    public function getCountry(): string
    {
        $countryCode = $this->getCountryCode();
        $country = get_country_name($countryCode);
        if (!$country) {
            throw new \Exception('User Country not found');
        }
        return $country;
    }
    public function getPostalCode(): string
    {
        $postalCode = get_user_meta($this->user->ID, 'postal_code', true);
        if (!$postalCode) {
            throw new \Exception('User address not found');
        }
        return $postalCode;
    }

    public function isPlanningToRentCar(): bool
    {
        $rentCar = get_user_meta($this->user->ID, 'rent_car', true);
        if (!$rentCar) {
            throw new \Exception('User address not found');
        }
        return $rentCar;
    }

    public function getLicence(): string
    {
        $licence = get_user_meta($this->user->ID, 'licence', true);
        if (!$licence) {
            throw new \Exception('User address not found');
        }
        return $licence;
    }


    public function getLicenseCountryOfIssueCode(): string
    {
        $licenseCountryOfIssue = get_user_meta($this->user->ID, 'license_country_of_issue', true);
        if (!$licenseCountryOfIssue) {
            throw new \Exception('User address not found');
        }
        return $licenseCountryOfIssue;
    }

    public function getLicenseCountryOfIssue(): string
    {
        $licenseCountryOfIssue = get_country_name($this->getLicenseCountryOfIssueCode());
        if (!$licenseCountryOfIssue) {
            throw new \Exception('User address not found');
        }
        return $licenseCountryOfIssue;
    }

    public function getLicenseExpiration(): string
    {
        $licenseExpiration = get_user_meta($this->user->ID, 'license_expiration', true);
        if (!$licenseExpiration) {
            throw new \Exception('User address not found');
        }
        return $licenseExpiration;
    }

    public function isRepeatingCustomer(): bool
    {

        $rental_agreements = ExtendedRentalsMetaModel::filterByQueries(
            CB::metaQuery(
                ['status-eq' => RentalStatus::COMPLETED->value,'user-eq' => $this->getId()]
            )
        );

        return count($rental_agreements) > 0;
    }

    public function getFormattedAddress(): string
    {
        $address = $this->getAddress();
        $apartment = $this->getApartment();
        $city = $this->getCity();
        $stateProvince = $this->getStateProvince();
        $country = $this->getCountry();
        $postalCode = $this->getPostalCode();

        return implode("\n", [$address, $apartment, $city, $stateProvince, $country, $postalCode]);
    }


    public function hasUploadedLicence(): bool
    {
        $licence = get_user_meta($this->user->ID, 'licence', true);
        return !empty($licence);
    }
}
