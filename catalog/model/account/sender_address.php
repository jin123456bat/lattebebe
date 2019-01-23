<?php
class ModelAccountSenderAddress extends Model {
	public function addAddress($customer_id, $data) {
		
		//$sql = "INSERT INTO " . DB_PREFIX . "sender_address SET customer_id = '" . (int)$customer_id . "', fullname = '" . $this->db->escape((string)$data['fullname']) . "', telephone = '" . $this->db->escape((string)$data['telephone']) . "', company = '" . $this->db->escape((string)$data['company']) . "', address_1 = '" . $this->db->escape((string)$data['address_1']) . "', address_2 = '" . $this->db->escape((string)$data['address_2']) . "', postcode = '" . $this->db->escape((string)$data['postcode']) . "', city = '" . $this->db->escape((string)$data['city']) . "', city_id = '" . $this->db->escape((string)$data['city_id']) . "', county_id = '" . $this->db->escape((string)$data['county_id']). . "', zone_id = '" . (int)$data['zone_id'] . "', country_id = '" . (int)$data['country_id'] . "', custom_field = '" . $this->db->escape(isset($data['custom_field']['address']) ? json_encode($data['custom_field']['address']) : '') . "'";
		
		//$this->db->query($sql);
		$data['customer_id'] = $customer_id;
		$sql = 'desc '.DB_PREFIX.'sender_address';
		$table = $this->db->query($sql);
		$fields = array_column($table->rows, 'Field');
		
		
		$sql = 'insert into '.DB_PREFIX.'sender_address  set ';
		$string = [];
		foreach ($data as $k => $v)
		{
			if (in_array($k, $fields))
			{
				$string[] = ($k.'="'.$this->db->escape($v).'"');
			}
		}
		$sql .= implode(',', $string);
		$this->db->query($sql);

		$address_id = $this->db->getLastId();

		if (!empty($data['default'])) {
			$this->db->query("UPDATE " . DB_PREFIX . "customer SET sender_address_id = '" . (int)$address_id . "' WHERE customer_id = '" . (int)$customer_id . "'");
		}

		return $address_id;
	}

	public function editAddress($address_id, $data) {
		$this->db->query("UPDATE " . DB_PREFIX . "sender_address SET fullname = '" . $this->db->escape((string)$data['fullname']) . "', telephone = '" . $this->db->escape((string)$data['telephone']) . "', company = '" . $this->db->escape((string)$data['company']) . "', address_1 = '" . $this->db->escape((string)$data['address_1']) . "', address_2 = '" . $this->db->escape((string)$data['address_2']) . "', postcode = '" . $this->db->escape((string)$data['postcode']) . "', city = '" . $this->db->escape((string)$data['city']) . "', city_id = '" . $this->db->escape((string)$data['city_id']) . "', county_id = '" . $this->db->escape((string)$data['county_id']) . "', zone_id = '" . (int)$data['zone_id'] . "', country_id = '" . (int)$data['country_id'] . "', custom_field = '" . $this->db->escape(isset($data['custom_field']['address']) ? json_encode($data['custom_field']['address']) : '') . "' WHERE address_id  = '" . (int)$address_id . "' AND customer_id = '" . (int)$this->customer->getId() . "'");

		if (!empty($data['default'])) {
			$this->db->query("UPDATE " . DB_PREFIX . "customer SET sender_address_id = '" . (int)$address_id . "' WHERE customer_id = '" . (int)$this->customer->getId() . "'");
		}
	}

	public function deleteAddress($address_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "sender_address WHERE address_id = '" . (int)$address_id . "' AND customer_id = '" . (int)$this->customer->getId() . "'");
	}

	public function getAddress($address_id) {
		$address_query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "sender_address WHERE address_id = '" . (int)$address_id . "' AND customer_id = '" . (int)$this->customer->getId() . "'");
		return $address_query->row;
		
		if ($address_query->num_rows) {
			$country_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "country` WHERE country_id = '" . (int)$address_query->row['country_id'] . "'");

			if ($country_query->num_rows) {
				$country = $country_query->row['name'];
				$iso_code_2 = $country_query->row['iso_code_2'];
				$iso_code_3 = $country_query->row['iso_code_3'];
				$address_format = $country_query->row['address_format'];
			}

			$zone_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "zone` WHERE zone_id = '" . (int)$address_query->row['zone_id'] . "'");

			if ($zone_query->num_rows) {
				$zone = $zone_query->row['name'];
				$zone_code = $zone_query->row['code'];
			}

			$address_data = array(
				'address_id'     => $address_query->row['address_id'],
				'fullname'      => $address_query->row['fullname'],
				'telephone'      => $address_query->row['telephone'],
				'company'        => $address_query->row['company'],
				'address_1'      => $address_query->row['address_1'],
				'address_2'      => $address_query->row['address_2'],
				'postcode'       => $address_query->row['postcode'],
				'city'           => $address_query->row['city'],
				'zone_id'        => $address_query->row['zone_id'],
				'zone'           => $zone,
				'zone_code'      => $zone_code,
				'country_id'     => $address_query->row['country_id'],
				'country'        => $country,
				'iso_code_2'     => $iso_code_2,
				'iso_code_3'     => $iso_code_3,
				'address_format' => $address_format,
                'city_id'        => $address_query->row['city_id'],
                'county_id'      => $address_query->row['county_id'],
				'custom_field'   => json_decode($address_query->row['custom_field'], true)
			);

			return $address_data;
		} else {
			return false;
		}
	}

	public function getAddresses() {
		$address_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "sender_address WHERE customer_id = '" . (int)$this->customer->getId() . "'");

		foreach ($query->rows as $result) {
			$country_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "country` WHERE country_id = '" . (int)$result['country_id'] . "'");

			if ($country_query->num_rows) {
				$country = $country_query->row['name'];
				$iso_code_2 = $country_query->row['iso_code_2'];
				$iso_code_3 = $country_query->row['iso_code_3'];
				$address_format = $country_query->row['address_format'];
			} else {
				$country = '';
				$iso_code_2 = '';
				$iso_code_3 = '';
				$address_format = '';
			}

			$zone_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "zone` WHERE zone_id = '" . (int)$result['zone_id'] . "'");

			if ($zone_query->num_rows) {
				$zone = $zone_query->row['name'];
				$zone_code = $zone_query->row['code'];
			} else {
				$zone = '';
				$zone_code = '';
			}

			if (is_free_or_pro()) {
			    $city_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "city` WHERE city_id = '" . (int)$result['city_id'] . "'");

			    if ($city_query->num_rows) {
                    $city = $city_query->row['name'];
                } else {
                    $city = '';
                }

                $county_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "city` WHERE city_id = '" . (int)$result['county_id'] . "'");

			    if ($county_query->num_rows) {
                    $county = $county_query->row['name'];
                } else {
                    $county = '';
                }
            } else {
                $city = $result['city'];
                $county = '';
            }

			$address_data[$result['address_id']] = array(
				'address_id'     => $result['address_id'],
				'fullname'       => $result['fullname'],
				'telephone'      => $result['telephone'],
				'company'        => $result['company'],
				'address_1'      => $result['address_1'],
				'address_2'      => $result['address_2'],
				'postcode'       => $result['postcode'],
				'city'           => $city,
                'county'         => $county,
				'zone_id'        => $result['zone_id'],
				'zone'           => $zone,
				'zone_code'      => $zone_code,
				'country_id'     => $result['country_id'],
				'country'        => $country,
				'iso_code_2'     => $iso_code_2,
				'iso_code_3'     => $iso_code_3,
				'address_format' => $address_format,
				'custom_field'   => json_decode($result['custom_field'], true)
			);
		}

		return $address_data;
	}

	public function getTotalAddresses() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "sender_address WHERE customer_id = '" . (int)$this->customer->getId() . "'");

		return $query->row['total'];
	}
}
