<?php
/* All rights reserved belong to the module, the module developers http://opencartadmin.com */
// http://opencartadmin.com © 2011-2017 All Rights Reserved
// Distribution, without the author's consent is prohibited
// Commercial license
if (!class_exists('ModelRecordRecord', false)) {
class ModelRecordRecord extends Model
{
	public function updateViewed($record_id)
	{
		$this->db->query("UPDATE " . DB_PREFIX . "record SET viewed = (viewed + 1) WHERE record_id = '" . (int) $record_id . "'");
	}

	public function getRecord($record_id) {
		$ascp_settings = $this->config->get('ascp_settings');
        $record_id = (int)$record_id;
		$ascp_customer_groups = $this->config->get('ascp_customer_groups');
		if (!empty($ascp_customer_groups)) {
			$customer_query_in = $this->db->escape("(" . implode(',', $this->config->get('ascp_customer_groups')) . ")");
		} else {
			$customer_query_in = "(-999)";
		}
		$customer_query = " AND raccess.customer_group_id IN " . $customer_query_in . " ";

		$sql            = "
		SELECT DISTINCT *, pd.name AS name, p.image, ";

        if (isset($ascp_settings['reviews_widget_status']) && $ascp_settings['reviews_widget_status']) {
			$sql            .= "
			(SELECT AVG(rating) AS total FROM " . DB_PREFIX . "comment r1
			WHERE
			r1.record_id = p.record_id
			AND
			r1.status = '1'
			AND
			r1.rating_mark = '0'
			GROUP BY r1.record_id) AS rating,
			(SELECT COUNT(*) AS total FROM " . DB_PREFIX . "comment r2
			WHERE
			r2.record_id = p.record_id
			AND
			r2.status = '1'
			GROUP BY r2.record_id) AS comments, ";
        }

		$sql            .= "
		p.sort_order

		FROM " . DB_PREFIX . "record p
		LEFT JOIN " . DB_PREFIX . "record_description pd ON (p.record_id = pd.record_id)
		LEFT JOIN " . DB_PREFIX . "record_to_store p2s ON (p.record_id = p2s.record_id)
		LEFT JOIN " . DB_PREFIX . "record_access raccess ON (raccess.record_id = p.record_id)

		WHERE p.record_id = '" . (int) $record_id . "'
		" . $customer_query . "
		AND
		pd.language_id = '" . (int) $this->config->get('config_language_id') . "'
		AND
		p.status = '1'
		AND NOW() BETWEEN p.date_available AND p.date_end
		AND
		p2s.store_id = '" . (int) $this->config->get('config_store_id') . "'";

        unset($ascp_settings);
		$query = $this->db->query($sql);

		if ($query->num_rows) {
			if (isset($query->row['rating'])) {
				$query->row['rating'] = round($query->row['rating']);
			}
			return $query->row;
		} else {
			return false;
		}
	}

	public function getRelatedRecords($pointer_id, $data, $pointer = 'product_id') {

		$pointer = $this->db->escape($pointer);
        $record_data = array();
        $sql_blogs = '';

		if ($pointer_id) {
			$ascp_customer_groups = $this->config->get('ascp_customer_groups');

			if (!empty($ascp_customer_groups)) {
				$customer_query_in = $this->db->escape("(" . implode(',', $this->config->get('ascp_customer_groups')) . ")");
			} else {
				$customer_query_in = "(-999)";
			}

			$customer_query = " AND raccess.customer_group_id IN " . $this->db->escape($customer_query_in) . " ";

			if (isset($data['settings']['blogs']) && isset($data['settings']['blogs'][0]) && $data['settings']['blogs'][0] > 0 && $data['settings']['blogs'][0] != -1) {
				$sql_blogs = $this->db->escape("AND	p2c.blog_id IN (" . implode(',', $data['settings']['blogs']) . ") ");
			}
			if (isset($data['pagination']) && $data['pagination']) {
				$calc_rows = " SQL_CALC_FOUND_ROWS ";
			} else {
				$calc_rows = "";
			}

$sql = "SELECT " . $calc_rows . " *, p.record_id as record_id FROM " . DB_PREFIX . "record_related pr
		LEFT JOIN " . DB_PREFIX . "record p ON (pr.pointer_id = p.record_id)
		LEFT JOIN " . DB_PREFIX . "record_to_store p2s ON (p.record_id = p2s.record_id)
	  	LEFT JOIN " . DB_PREFIX . "record_to_blog p2c ON (p.record_id = p2c.record_id)
	  	LEFT JOIN " . DB_PREFIX . "record_access raccess ON (raccess.record_id = p.record_id)
		WHERE
		 pr.related_id = '" . (int) $pointer_id . "' AND pointer='" . $pointer . "'
		 " . $sql_blogs . "
		 AND p.status = '1'
		 " . $customer_query . "
		 AND NOW() BETWEEN p.date_available AND p.date_end
		 AND p2s.store_id = '" . (int) $this->config->get('config_store_id') . "'";
		$sql .= " GROUP BY p.record_id ";

			$sort_data = array(
				'rating',
				'comments',
				'popular',
				'latest',
				'sort'
			);
			if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
				if ($data['sort'] == 'rating') {
					$data['sort'] = 'rating';
				}
				if ($data['sort'] == 'comments') {
					$data['sort'] = 'comments';
				}
				if ($data['sort'] == 'latest') {
					$data['sort'] = 'p.date_available';
				}
				if ($data['sort'] == 'sort') {
					$data['sort'] = 'p.sort_order';
				}
				if ($data['sort'] == 'popular') {
					$data['sort'] = 'p.viewed';
				}
				$sql .= " ORDER BY " . $data['sort'];
			} else {
				$sql .= " ORDER BY p.sort_order";
			}
			if (isset($data['order']) && (strtoupper($data['order']) == 'DESC')) {
				$sql .= " DESC";
			} else {
				$sql .= " ASC";
			}
			if (isset($data['start']['start'])) {
				$data['start'] = $data['start']['start'];
			} else {
				$data['start'] = 0;
			}
			if (isset($data['start']) || isset($data['limit'])) {
				if ($data['start'] < 0) {
					$data['start'] = 0;
				}
				if ($data['limit'] < 1) {
					$data['limit'] = 20;
				}
				$sql .= " LIMIT " . (int) $data['start'] . "," . (int) $data['limit'];
			}
			$query = $this->db->query($sql);
			//if (isset($data['pagination']) && $data['pagination']) {
				$query_total = $this->db->query("SELECT FOUND_ROWS() as total");
				$total       = $query_total->row['total'];
			//} else {
			//	$total = false;
			//}
			foreach ($query->rows as $num => $result) {
				$query->rows[$num]['total'] = $total;
			}
			return $query->rows;
		}
	}


	public function getRelatedRecordsDate($pointer_id, $data, $pointer = 'record_id_date')
	{
		$pointer = $this->db->escape($pointer);
		if ($this->registry->get('getRelatedRecordsDate') > 1) return array();

		if ($pointer_id) {
			$ascp_customer_groups = $this->config->get('ascp_customer_groups');
			if (!empty($ascp_customer_groups)) {
				$customer_query_in = $this->db->escape("(" . implode(',', $this->config->get('ascp_customer_groups')) . ")");
			} else {
				$customer_query_in = "(-999)";
			}
			$customer_query = "AND raccess.customer_group_id IN " . $customer_query_in . " ";

			if (isset($data['pagination']) && $data['pagination']) {
				$calc_rows = " SQL_CALC_FOUND_ROWS ";
			} else {
				$calc_rows = "";
			}

			if ($data['blog_main']) {
				$sql_blog_main = "AND p.blog_main = '".(int)$data['blog_id']."' ";
			} else {
				$sql_blog_main = $this->db->escape("AND p2c.blog_id IN (" . implode(',', $data['blog_id']) . ") ");
			}

			$record_data = array();
			$sql         = "SELECT " . $calc_rows . " *, p.record_id as record_id, p.blog_main as blog_main FROM " . DB_PREFIX . "record p
LEFT JOIN " . DB_PREFIX . "record_to_store p2s ON (p.record_id = p2s.record_id)
LEFT JOIN " . DB_PREFIX . "record_to_blog p2c ON (p.record_id = p2c.record_id)
LEFT JOIN " . DB_PREFIX . "record_access raccess ON (raccess.record_id = p.record_id)
WHERE
p.status = '1'
".$sql_blog_main."
AND p.record_id <> '".(int)$pointer_id."'
" . $customer_query . "
AND UNIX_TIMESTAMP(p.date_available) ".$this->db->escape($data['related_date_compare_str'])." UNIX_TIMESTAMP('".$this->db->escape($data['record_data']['date_available'])."')
AND NOW() BETWEEN p.date_available AND p.date_end
AND p2s.store_id = '" . (int) $this->config->get('config_store_id') . "'
";

			$sql .= " GROUP BY p.record_id ";
			$sort_data = array(
				'rating',
				'comments',
				'popular',
				'latest',
				'sort'
			);
			if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
				if ($data['sort'] == 'rating') {
					$data['sort'] = 'rating';
				}
				if ($data['sort'] == 'comments') {
					$data['sort'] = 'comments';
				}
				if ($data['sort'] == 'latest') {
					$data['sort'] = 'p.date_available';
				}
				if ($data['sort'] == 'sort') {
					$data['sort'] = 'p.sort_order';
				}
				if ($data['sort'] == 'popular') {
					$data['sort'] = 'p.viewed';
				}
				$sql .= " ORDER BY " . $data['sort'];
			} else {
				$sql .= " ORDER BY p.sort_order";
			}
			if (isset($data['order']) && (strtoupper($data['order']) == 'DESC')) {
				$sql .= " DESC";
			} else {
				$sql .= " ASC";
			}
			if (isset($data['start']['start'])) {
				$data['start'] = $data['start']['start'];
			} else {
				$data['start'] = 0;
			}
			if (isset($data['start']) || isset($data['limit'])) {
				if ($data['start'] < 0) {
					$data['start'] = 0;
				}
				if ($data['limit'] < 1) {
					$data['limit'] = 20;
				}
				$sql .= " LIMIT " . (int) $data['start'] . "," . (int) $data['limit'];
			}
			$query = $this->db->query($sql);

			$query_total = $this->db->query("SELECT FOUND_ROWS() as total");
			$total       = $query_total->row['total'];

			foreach ($query->rows as $num => $result) {
				$query->rows[$num]['total'] = $total;
			}

            $rows = $query->rows;

            if ($total == 0) {

                $this->registry->set('getRelatedRecordsDate', $this->registry->get('getRelatedRecordsDate') + 1);

                if ($data['related_date_compare_str'] == '<') {
                	$data['related_date_compare_str'] = '>';
                } else {
                	$data['related_date_compare_str'] = '<';
                }

            	if (isset($data['order']) && (strtoupper($data['order']) == 'DESC')) {
					$data['order']= 'ASC';
				} else {
					$data['order']= 'DESC';
				}
            	$rows = $this->getRelatedRecordsDate($pointer_id, $data, $pointer);
            }

			return $rows;
		}
	}


	public function getBlogMainByRecord($record_id)
	{
		if ($record_id < 1) {
			return false;
		} else {
			$record_id = (int) $record_id;
		}
		$sql = "SELECT blog_id
		FROM
		" . DB_PREFIX . "record_to_blog r2b
		LEFT JOIN " . DB_PREFIX . "record r ON (r.record_id = r2b.record_id)
		WHERE
		r2b.record_id = '" . $record_id . "'
		AND
		r.blog_main = r2b.blog_id
		";

		$query = $this->db->query($sql);

		return $query->row['blog_id'];
	}

	public function getRecords($data = array()) {
		$ascp_settings = $this->config->get('ascp_settings');
		$ascp_customer_groups = $this->config->get('ascp_customer_groups');
		if (!empty($ascp_customer_groups)) {
			$customer_query_in = $this->db->escape("(" . implode(',', $this->config->get('ascp_customer_groups')) . ")");
		} else {
			$customer_query_in = "(-999)";
		}
		$customer_query = " AND raccess.customer_group_id IN " . $customer_query_in . " ";
		$cache          = md5(http_build_query($data) . $customer_query_in . $customer_query);
		$cache_file     = 'blog.record.' . (int) $this->config->get('config_language_id') . '.' . (int) $this->config->get('config_store_id') . '.' . $cache;
		$record_data    = $this->cache->get($cache_file);
		if (!$record_data) {
			$sql = "SELECT p.* ";

			if (isset($ascp_settings['reviews_widget_status']) && $ascp_settings['reviews_widget_status']) {
				$sql .= ", (SELECT COUNT(*) AS total FROM " . DB_PREFIX . "comment rc
				WHERE rc.record_id = p.record_id AND rc.status = '1' GROUP BY rc.record_id) AS comments,
				(SELECT AVG(rating) AS total FROM " . DB_PREFIX . "comment r1
				WHERE r1.record_id = p.record_id AND r1.status = '1'
				AND
				r1.rating_mark = '0'
				GROUP BY r1.record_id) AS rating ";
			}

            $sql .= "
			FROM " . DB_PREFIX . "record p
			LEFT JOIN " . DB_PREFIX . "record_description pd ON (p.record_id = pd.record_id)
			LEFT JOIN " . DB_PREFIX . "record_to_store p2s ON (p.record_id = p2s.record_id)
			LEFT JOIN " . DB_PREFIX . "record_access raccess ON (raccess.record_id = p.record_id)
			";
			if (!empty($data['filter_tag'])) {
				$sql .= " LEFT JOIN " . DB_PREFIX . "record_tag pt ON (p.record_id = pt.record_id)";
			}
			if (!empty($data['filter_blog_id'])) {
				$sql .= " LEFT JOIN " . DB_PREFIX . "record_to_blog p2c ON (p.record_id = p2c.record_id)";
			}
			$sql .= " WHERE pd.language_id = '" . (int) $this->config->get('config_language_id') . "'
			AND p.status = '1'
 		    " . $customer_query . "
			AND NOW() BETWEEN p.date_available AND p.date_end
			AND p2s.store_id = '" . (int) $this->config->get('config_store_id') . "'";
			if (!empty($data['filter_name']) || !empty($data['filter_tag'])) {
				$sql .= " AND (";
				if (!empty($data['filter_name'])) {
					$implode = array();
					$words   = explode(' ', $data['filter_name']);
					foreach ($words as $word) {
						if (!empty($data['filter_description'])) {
							$implode[] = "LCASE(pd.name) LIKE '%" . $this->db->escape(utf8_strtolower($word)) . "%' OR LCASE(pd.description) LIKE '%" . $this->db->escape(utf8_strtolower($word)) . "%'";
						} else {
							$implode[] = "LCASE(pd.name) LIKE '%" . $this->db->escape(utf8_strtolower($word)) . "%'";
						}
					}
					if ($implode) {
						$sql .= " " . implode(" OR ", $implode) . "";
					}
				}
				if (!empty($data['filter_name']) && !empty($data['filter_tag'])) {
					$sql .= " OR ";
				}
				if (!empty($data['filter_tag'])) {
					$implode = array();
					$words   = explode(' ', $data['filter_tag']);
					foreach ($words as $word) {
						$implode[] = "LCASE(pt.tag) LIKE '%" . $this->db->escape(utf8_strtolower($data['filter_tag'])) . "%' AND pt.language_id = '" . (int) $this->config->get('config_language_id') . "'";
					}
					if ($implode) {
						$sql .= " " . implode(" OR ", $implode) . "";
					}
				}
				$sql .= ")";
			}
			if (!empty($data['filter_author'])) {
				$sql .= " AND (";
				if (!empty($data['filter_author'])) {
					$sql .= "LCASE(p.author) LIKE '%" . $this->db->escape(utf8_strtolower($data['filter_author'])) . "%'";
				}
				$sql .= ")";
			}
			if (!empty($data['filter_blog_id'])) {
				if (!empty($data['filter_sub_blog'])) {
					$implode_data   = array();
					$implode_data[] = "p2c.blog_id = '" . (int) $data['filter_blog_id'] . "'";
					$this->load->model('record/blog');
					$blogies = $this->model_record_blog->getBlogiesByParentId($data['filter_blog_id']);
					foreach ($blogies as $blog_id) {
						$implode_data[] = "p2c.blog_id = '" . (int) $blog_id . "'";
					}
					$sql .= " AND (" . implode(' OR ', $implode_data) . ")";
				} else {
					$sql .= " AND p2c.blog_id = '" . (int) $data['filter_blog_id'] . "'";
				}
			}
			$sql .= " GROUP BY p.record_id";
			$sort_data = array(
				'pd.name',
				'rating',
				'comments',
				'p.viewed',
				'p.sort_order',
				'p.date_added',
				'p.date_available'
			);
			if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
				if ($data['sort'] == 'pd.name' || $data['sort'] == 'p.model') {
					$sql .= " ORDER BY LCASE(" . $data['sort'] . ")";
				} else {
					$sql .= " ORDER BY " . $data['sort'];
				}
			} else {
				$sql .= " ORDER BY p.sort_order ";
			}
			if (isset($data['order']) && (strtoupper($data['order']) == 'DESC')) {
				$sql .= " DESC";
			} else {
				$sql .= " ASC";
			}
			if (isset($data['start']) || isset($data['limit'])) {
				if ($data['start'] < 0) {
					$data['start'] = 0;
				}
				if ($data['limit'] < 1) {
					$data['limit'] = 20;
				}
				$sql .= " LIMIT " . (int) $data['start'] . "," . (int) $data['limit'];
			}
			$record_data = array();
			unset($ascp_settings);
			$query       = $this->db->query($sql);
			foreach ($query->rows as $result) {
				$record_data[$result['record_id']] = $this->getRecord($result['record_id']);
			}
			$this->cache->set($cache_file, $record_data);
		}
		return $record_data;
	}



	public function getRecordsSitemap($data = array()) {
		$ascp_settings = $this->config->get('ascp_settings');
		$ascp_customer_groups = $this->config->get('ascp_customer_groups');
		if (!empty($ascp_customer_groups)) {
			$customer_query_in = $this->db->escape("(" . implode(',', $this->config->get('ascp_customer_groups')) . ")");
		} else {
			$customer_query_in = "(-999)";
		}
		$customer_query = " AND raccess.customer_group_id IN " . $customer_query_in . " ";
		$cache          = md5(http_build_query($data) . $customer_query_in . $customer_query);
		$cache_file     = 'blog.records.sitemap.' . (int) $this->config->get('config_language_id') . '.' . (int) $this->config->get('config_store_id') . '.' . $cache;
		$record_data    = $this->cache->get($cache_file);
		if (!$record_data) {
			$sql = "SELECT p.* ";

            $sql .= "
			FROM " . DB_PREFIX . "record p
			LEFT JOIN " . DB_PREFIX . "record_description pd ON (p.record_id = pd.record_id)
			LEFT JOIN " . DB_PREFIX . "record_to_store p2s ON (p.record_id = p2s.record_id)
			LEFT JOIN " . DB_PREFIX . "record_access raccess ON (raccess.record_id = p.record_id)
			";
			$sql .= " WHERE pd.language_id = '" . (int) $this->config->get('config_language_id') . "'
			AND p.status = '1'
 		    " . $customer_query . "
			AND NOW() BETWEEN p.date_available AND p.date_end
			AND p2s.store_id = '" . (int) $this->config->get('config_store_id') . "'";

			$sql .= " GROUP BY p.record_id";

			$record_data = array();
			unset($ascp_settings);
			$query       = $this->db->query($sql);
			foreach ($query->rows as $result) {
				$record_data[$result['record_id']] = $this->getRecord($result['record_id']);
			}
			$this->cache->set($cache_file, $record_data);
		}
		return $record_data;
	}



	public function getRecordCategories($record_id)
	{
		$record_blog_data = array();
		$sql              = "SELECT * FROM " . DB_PREFIX . "record_to_blog WHERE record_id = '" . (int) $record_id . "'";
		$query            = $this->db->query($sql);
		foreach ($query->rows as $result) {
			$record_blog_data[] = $result['blog_id'];
		}
		return $record_blog_data;
	}

	public function getLatestRecords($limit)
	{
		$ascp_customer_groups = $this->config->get('ascp_customer_groups');
		if (!empty($ascp_customer_groups)) {
			$customer_query_in = $this->db->escape("(" . implode(',', $this->config->get('ascp_customer_groups')) . ")");
		} else {
			$customer_query_in = "(-999)";
		}
		$customer_query = " AND raccess.customer_group_id IN " . $customer_query_in . " ";
		$record_data    = $this->cache->get('blog.record.latest.' . (int) $this->config->get('config_language_id') . '.' . (int) $this->config->get('config_store_id') . '.' . (int) $limit);
		if (!$record_data) {
			$query = $this->db->query("
			SELECT p.record_id FROM " . DB_PREFIX . "record p
			LEFT JOIN " . DB_PREFIX . "record_to_store p2s ON (p.record_id = p2s.record_id)
			LEFT JOIN " . DB_PREFIX . "record_access raccess ON (raccess.record_id = p.record_id)
			WHERE p.status = '1'
			" . $customer_query . "
			AND NOW() BETWEEN p.date_available AND p.date_end
			AND p2s.store_id = '" . (int) $this->config->get('config_store_id') . "' ORDER BY p.date_added DESC LIMIT " . (int) $limit);
			foreach ($query->rows as $result) {
				$record_data[$result['record_id']] = $this->getRecord($result['record_id']);
			}
			$this->cache->set('blog.record.latest.' . (int) $this->config->get('config_language_id') . '.' . (int) $this->config->get('config_store_id') . '.' . (int) $limit, $record_data);
		}
		return $record_data;
	}

	public function getPopularRecords($limit)
	{
		$ascp_customer_groups = $this->config->get('ascp_customer_groups');
		if (!empty($ascp_customer_groups)) {
			$customer_query_in = $this->db->escape("(" . implode(',', $this->config->get('ascp_customer_groups')) . ")");
		} else {
			$customer_query_in = "(-999)";
		}
		$customer_query = " AND raccess.customer_group_id IN " . $customer_query_in . " ";
		$record_data    = array();
		$query          = $this->db->query("
		SELECT p.record_id FROM " . DB_PREFIX . "record p
		LEFT JOIN " . DB_PREFIX . "record_to_store p2s ON (p.record_id = p2s.record_id)
		LEFT JOIN " . DB_PREFIX . "record_access raccess ON (raccess.record_id = p.record_id)

		 WHERE p.status = '1'
		 " . $customer_query . "
		 AND NOW() BETWEEN p.date_available AND p.date_end
		 AND p2s.store_id = '" . (int) $this->config->get('config_store_id') . "' ORDER BY p.viewed, p.date_added DESC LIMIT " . (int) $limit);
		foreach ($query->rows as $result) {
			$record_data[$result['record_id']] = $this->getRecord($result['record_id']);
		}
		return $record_data;
	}

	public function getBestSellerRecords($limit)
	{
		$ascp_customer_groups = $this->config->get('ascp_customer_groups');
		if (!empty($ascp_customer_groups)) {
			$customer_query_in = $this->db->escape("(" . implode(',', $this->config->get('ascp_customer_groups')) . ")");
		} else {
			$customer_query_in = "(-999)";
		}
		$customer_query = " AND raccess.customer_group_id IN " . $customer_query_in . " ";
		$record_data    = $this->cache->get('blog.record.bestseller.' . (int) $this->config->get('config_language_id') . '.' . (int) $this->config->get('config_store_id') . '.' . (int) $limit);
		if (!$record_data) {
			$record_data = array();
			$query       = $this->db->query("
			SELECT op.record_id, COUNT(*) AS total FROM " . DB_PREFIX . "order_record op
			LEFT JOIN `" . DB_PREFIX . "order` o ON (op.order_id = o.order_id)
			LEFT JOIN `" . DB_PREFIX . "record` p ON (op.record_id = p.record_id)
			LEFT JOIN " . DB_PREFIX . "record_access raccess ON (raccess.record_id = p.record_id)
			LEFT JOIN " . DB_PREFIX . "record_to_store p2s ON (p.record_id = p2s.record_id) WHERE o.order_status_id > '0'

			AND p.status = '1'
			 " . $customer_query . "
			 AND NOW() BETWEEN p.date_available AND p.date_end
			 AND p2s.store_id = '" . (int) $this->config->get('config_store_id') . "' GROUP BY op.record_id ORDER BY total DESC LIMIT " . (int) $limit);
			foreach ($query->rows as $result) {
				$record_data[$result['record_id']] = $this->getRecord($result['record_id']);
			}
			$this->cache->set('blog.record.bestseller.' . (int) $this->config->get('config_language_id') . '.' . (int) $this->config->get('config_store_id') . '.' . (int) $limit, $record_data);
		}
		return $record_data;
	}

	public function getRecordAttributes($record_id)
	{
		$record_attribute_group_data  = array();
		$sql                          = "SELECT ag.attribute_group_id, agd.name FROM " . DB_PREFIX . "record_attribute pa
		LEFT JOIN " . DB_PREFIX . "attribute a ON (pa.attribute_id = a.attribute_id)
		LEFT JOIN " . DB_PREFIX . "attribute_group ag ON (a.attribute_group_id = ag.attribute_group_id)
		LEFT JOIN " . DB_PREFIX . "attribute_group_description agd ON (ag.attribute_group_id = agd.attribute_group_id)
		WHERE
		pa.record_id = '" . (int) $record_id . "'
		AND
		agd.language_id = '" . (int) $this->config->get('config_language_id') . "'
		GROUP BY ag.attribute_group_id
		ORDER BY ag.sort_order, agd.name";
		$record_attribute_group_query = $this->db->query($sql);
		foreach ($record_attribute_group_query->rows as $record_attribute_group) {
			$record_attribute_data  = array();
			$sql_pod                = "SELECT a.attribute_id, ad.name, pa.text FROM " . DB_PREFIX . "record_attribute pa
			LEFT JOIN " . DB_PREFIX . "attribute a ON (pa.attribute_id = a.attribute_id)
			LEFT JOIN " . DB_PREFIX . "attribute_description ad ON (a.attribute_id = ad.attribute_id)
			WHERE pa.record_id = '" . (int) $record_id . "'
			AND a.attribute_group_id = '" . (int) $record_attribute_group['attribute_group_id'] . "'
			AND pa.language_id = '" . (int) $this->config->get('config_language_id') . "'
			ORDER BY a.sort_order, ad.name";
			$record_attribute_query = $this->db->query($sql_pod);
			foreach ($record_attribute_query->rows as $record_attribute) {
				$record_attribute_data[] = array(
					'attribute_id' => $record_attribute['attribute_id'],
					'name' => $record_attribute['name'],
					'text' => $record_attribute['text']
				);
			}
			$record_attribute_group_data[] = array(
				'attribute_group_id' => $record_attribute_group['attribute_group_id'],
				'name' => $record_attribute_group['name'],
				'attribute' => $record_attribute_data
			);
		}
		return $record_attribute_group_data;
	}

	public function getRecordImages($record_id, $images_number = false)
	{
		if ($images_number) {
			$sql_limit = " LIMIT 0," . (int) $images_number;
		} else {
			$sql_limit = "";
		}
		$sql   = "SELECT * FROM " . DB_PREFIX . "record_image
		WHERE record_id = '" . (int) $record_id . "'
		ORDER BY sort_order ASC " . $sql_limit;
		$query = $this->db->query($sql);
		return $query->rows;
	}

	public function getRecordRelated($record_id)
	{
		$ascp_customer_groups = $this->config->get('ascp_customer_groups');
		if (!empty($ascp_customer_groups)) {
			$customer_query_in = $this->db->escape("(" . implode(',', $this->config->get('ascp_customer_groups')) . ")");
		} else {
			$customer_query_in = "(-999)";
		}
		$customer_query = " AND raccess.customer_group_id IN " . $customer_query_in . " ";
		$record_data    = array();
		$sql = "SELECT * FROM " . DB_PREFIX . "record_related pr
		LEFT JOIN " . DB_PREFIX . "record p ON (pr.related_id = p.record_id)
		LEFT JOIN " . DB_PREFIX . "record_to_store p2s ON (p.record_id = p2s.record_id)
		LEFT JOIN " . DB_PREFIX . "record_access raccess ON (raccess.record_id = p.record_id)

		 WHERE pr.pointer_id = '" . (int) $record_id . "' AND pointer='record_id'
		 AND p.status = '1'
		 " . $customer_query . "
		 AND NOW() BETWEEN p.date_available AND p.date_end
		 AND p2s.store_id = '" . (int) $this->config->get('config_store_id') . "'";
		$query = $this->db->query($sql);
		foreach ($query->rows as $result) {
			$record_data[$result['related_id']] = $this->getRecord($result['related_id']);
		}
		return $record_data;
	}

	public function getProductRecord($pointer_id)
	{
		$query = $this->db->query("
		SELECT DISTINCT *, pd.name AS name, p.image, m.name AS manufacturer,
		(SELECT price FROM " . DB_PREFIX . "product_special ps WHERE ps.product_id = p.product_id AND ps.customer_group_id = '" . (int) $this->config->get('config_customer_group_id') . "' AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW())) ORDER BY ps.priority ASC, ps.price ASC LIMIT 1) AS special,
		(SELECT points FROM " . DB_PREFIX . "product_reward pr WHERE pr.product_id = p.product_id AND customer_group_id = '" . (int) $this->config->get('config_customer_group_id') . "') AS reward,
		(SELECT ss.name FROM " . DB_PREFIX . "stock_status ss WHERE ss.stock_status_id = p.stock_status_id AND ss.language_id = '" . (int) $this->config->get('config_language_id') . "') AS stock_status,
		(SELECT wcd.unit FROM " . DB_PREFIX . "weight_class_description wcd WHERE p.weight_class_id = wcd.weight_class_id AND wcd.language_id = '" . (int) $this->config->get('config_language_id') . "') AS weight_class,
		(SELECT lcd.unit FROM " . DB_PREFIX . "length_class_description lcd WHERE p.length_class_id = lcd.length_class_id AND lcd.language_id = '" . (int) $this->config->get('config_language_id') . "') AS length_class,
		(SELECT AVG(rating) AS total FROM " . DB_PREFIX . "review r1 WHERE r1.product_id = p.product_id AND r1.status = '1' AND r1.rating_mark = '0' GROUP BY r1.product_id) AS rating,
		(SELECT COUNT(*) AS total FROM " . DB_PREFIX . "review r2 WHERE r2.product_id = p.product_id AND r2.status = '1' GROUP BY r2.product_id) AS reviews, p.sort_order
		FROM " . DB_PREFIX . "product p
		LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id)
		LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id)
		LEFT JOIN " . DB_PREFIX . "manufacturer m ON (p.manufacturer_id = m.manufacturer_id)
		WHERE
		p.product_id = '" . (int) $pointer_id . "'
		AND pd.language_id = '" . (int) $this->config->get('config_language_id') . "'
		AND p.status = '1'
		AND p.date_available <= NOW()
		AND p2s.store_id = '" . (int) $this->config->get('config_store_id') . "'");

		if ($query->num_rows) {
			$query->row['price']  = $query->row['price'];
			$query->row['rating'] = round($query->row['rating']);
			return $query->row;
		} else {
			return false;
		}
	}

	public function getProductRelated($record_id, $pointer = 'product_id')
	{
		$pointer = $this->db->escape($pointer);
		$product_data = array();
		$sql = "SELECT  p.product_id
		FROM " . DB_PREFIX . "record_related pr
		LEFT JOIN " . DB_PREFIX . "product p ON (pr.related_id = p.product_id)
		LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id)
		WHERE pr.pointer_id = '" . (int) $record_id . "'
		AND p.status = '1' AND p.date_available <= NOW() AND pr.pointer='" . $pointer . "'
		AND p2s.store_id = '" . (int) $this->config->get('config_store_id') . "'";
		$query = $this->db->query($sql);

		foreach ($query->rows as $result) {
			$product_data[$result['product_id']] =  $this->model_catalog_product->getProduct($result['product_id']);
		}

		return $product_data;
	}

	public function getRecordTags($record_id)
	{
		$query = $this->db->query("
		SELECT * FROM " . DB_PREFIX . "record_tag WHERE record_id = '" . (int) $record_id . "' AND language_id = '" . (int) $this->config->get('config_language_id') . "'");
		return $query->rows;
	}

	public function getRecordLayoutId($record_id)
	{
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "record_to_layout WHERE record_id = '" . (int) $record_id . "' AND store_id = '" . (int) $this->config->get('config_store_id') . "'");
		if ($query->num_rows) {
			return $query->row['layout_id'];
		} else {
			return $this->config->get('config_layout_record');
		}
	}

	public function getBlogies($record_id)
	{
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "record_to_blog WHERE record_id = '" . (int) $record_id . "'");
		return $query->rows;
	}

	public function getTotalRecords($data = array()) {
		$ascp_customer_groups = $this->config->get('ascp_customer_groups');
		if (!empty($ascp_customer_groups)) {
			$customer_query_in = $this->db->escape("(" . implode(',', $this->config->get('ascp_customer_groups')) . ")");
		} else {
			$customer_query_in = "(-999)";
		}
		$customer_query = " AND raccess.customer_group_id IN " . $customer_query_in . " ";

		if (isset($this->session->data)) {
			$session = $this->session->data;
		} else {
			$session = array();
		}
		if (isset($session['token'])) {
		  	unset($session['token']);
		}
		if (isset($session['captcha'])) {
		  	unset($session['captcha']);
		}
        $cache_hash_file = md5(serialize($data));
		$cache_hash = md5($cache_hash_file.serialize($this->request->get).serialize($session).$customer_query.$this->config->get('config_language_id').$this->config->get('config_store_id'));
		$cache_file     = 'blog.total.record.' . (int) $this->config->get('config_language_id') . '.' . (int) $this->config->get('config_store_id') . '.' . $cache_hash_file;
		$record_data    = $this->cache->get($cache_file);
		if (!isset($record_data[$cache_hash])) {

			$sql            = "SELECT COUNT(DISTINCT p.record_id) AS total FROM " . DB_PREFIX . "record p
			LEFT JOIN " . DB_PREFIX . "record_description pd ON (p.record_id = pd.record_id)
			LEFT JOIN " . DB_PREFIX . "record_to_store p2s ON (p.record_id = p2s.record_id)
			LEFT JOIN " . DB_PREFIX . "record_access raccess ON (raccess.record_id = p.record_id)
			";
			if (!empty($data['filter_blog_id'])) {
				$sql .= " LEFT JOIN " . DB_PREFIX . "record_to_blog p2c ON (p.record_id = p2c.record_id)
						  LEFT JOIN " . DB_PREFIX . "blog pb ON (p2c.blog_id = pb.blog_id)
				";
			}
			if (!empty($data['filter_tag'])) {
				$sql .= " LEFT JOIN " . DB_PREFIX . "record_tag pt ON (p.record_id = pt.record_id)";
			}
			$sql .= " WHERE pd.language_id = '" . (int) $this->config->get('config_language_id') . "'
			AND p.status = '1'
			" . $customer_query . "
			AND NOW() BETWEEN p.date_available AND p.date_end
			AND p2s.store_id = '" . (int) $this->config->get('config_store_id') . "'";
			if (!empty($data['filter_name']) || !empty($data['filter_tag'])) {
				$sql .= " AND (";
				if (!empty($data['filter_name'])) {
					$implode = array();
					$words   = explode(' ', $data['filter_name']);
					foreach ($words as $word) {
						if (!empty($data['filter_description'])) {
							$implode[] = "LCASE(pd.name) LIKE '%" . $this->db->escape(utf8_strtolower($word)) . "%' OR LCASE(pd.description) LIKE '%" . $this->db->escape(utf8_strtolower($word)) . "%'";
						} else {
							$implode[] = "LCASE(pd.name) LIKE '%" . $this->db->escape(utf8_strtolower($word)) . "%'";
						}
					}
					if ($implode) {
						$sql .= " " . implode(" OR ", $implode) . "";
					}
				}
				if (!empty($data['filter_name']) && !empty($data['filter_tag'])) {
					$sql .= " OR ";
				}
				if (!empty($data['filter_tag'])) {
					$implode = array();
					$words   = explode(' ', $data['filter_tag']);
					foreach ($words as $word) {
						$implode[] = "LCASE(pt.tag) LIKE '%" . $this->db->escape(utf8_strtolower($data['filter_tag'])) . "%' AND pt.language_id = '" . (int) $this->config->get('config_language_id') . "'";
					}
					if ($implode) {
						$sql .= " " . implode(" OR ", $implode) . "";
					}
				}
				$sql .= ")";
			}
			if (!empty($data['filter_author'])) {
				$sql .= " AND (";
				if (!empty($data['filter_author'])) {
					$sql .= "LCASE(p.author) LIKE '%" . $this->db->escape(utf8_strtolower($data['filter_author'])) . "%'";
				}
				$sql .= ")";
			}
			if (!empty($data['filter_blog_id'])) {
				if (!empty($data['filter_sub_blog'])) {
					$implode_data   = array();
					$implode_data[] = "p2c.blog_id = '" . (int) $data['filter_blog_id'] . "'";
					$this->load->model('record/blog');
					$blogies = $this->model_record_blog->getBlogiesByParentId($data['filter_blog_id']);
					foreach ($blogies as $blog_id) {
						$implode_data[] = "p2c.blog_id = '" . (int) $blog_id . "'";
					}
					$sql .= " AND (" . implode(' OR ', $implode_data) . ")";
				} else {
					$sql .= " AND p2c.blog_id = '" . (int) $data['filter_blog_id'] . "'";
				}
			}
			if (!empty($data['filter_manufacturer_id'])) {
				$sql .= " AND p.manufacturer_id = '" . (int) $data['filter_manufacturer_id'] . "'";
			}

			$query = $this->db->query($sql);

			$total_count = $query->row['total'];
			if (is_array($record_data)) {
				$record_data[$cache_hash] = $total_count;
			} else {
            	$record_data = array();
            	$record_data[$cache_hash] = $total_count;
			}
	        $this->cache->set($cache_file, $record_data);
		} else {
			$total_count = $record_data[$cache_hash];
		}

		return $total_count;
	}

	public function getBlogsRecords($data = array()) {
		$ascp_settings = $this->config->get('ascp_settings');
		$ascp_customer_groups = $this->config->get('ascp_customer_groups');
		if (!empty($ascp_customer_groups)) {
			$customer_query_in = $this->db->escape("(" . implode(',', $this->config->get('ascp_customer_groups')) . ")");
		} else {
			$customer_query_in = "(-999)";
		}
		$customer_query = " AND raccess.customer_group_id IN " . $customer_query_in . " ";
		$calc_rows      = "SQL_CALC_FOUND_ROWS";
		$cache          = md5(http_build_query($data) . $customer_query_in . $customer_query);
		$cache_file     = 'blog.records.' . (int) $this->config->get('config_language_id') . '.' . (int) $this->config->get('config_store_id') . '.' . $cache;
		$record_data    = $this->cache->get($cache_file);
		if (!$record_data || $data['sort'] == 'rand') {
			$sql = "SELECT " . $calc_rows . " p.* ";
        if (isset($ascp_settings['reviews_widget_status']) && $ascp_settings['reviews_widget_status']) {
			$sql .= "
			, (SELECT COUNT(*) AS total FROM " . DB_PREFIX . "comment rc
			WHERE rc.record_id = p.record_id AND rc.status = '1' GROUP BY rc.record_id) AS comments,
			(SELECT AVG(rating) AS total FROM " . DB_PREFIX . "comment r1
	  		WHERE r1.record_id = p.record_id AND r1.status = '1'
			AND
			r1.rating_mark = '0'
	  		GROUP BY r1.record_id) AS rating ";
         }
          $sql            .= "
			FROM " . DB_PREFIX . "record p
			LEFT JOIN " . DB_PREFIX . "record_to_blog r2b ON (p.record_id = r2b.record_id AND p.status = 1)
			LEFT JOIN " . DB_PREFIX . "record_description pd ON (p.record_id = pd.record_id)
			LEFT JOIN " . DB_PREFIX . "record_to_store p2s ON (p.record_id = p2s.record_id)
			LEFT JOIN " . DB_PREFIX . "record_access raccess ON (raccess.record_id = p.record_id)
			";
			$sql .= " WHERE
			pd.language_id = '" . (int) $this->config->get('config_language_id') . "' " . $customer_query . " ";
			if (!empty($data['filter_blogs'])) {
				$sql .= " AND r2b.blog_id IN " . "(" . implode(',', $data['filter_blogs']) . ")" . " ";
			}
			$sql .= " AND p.status = '1'
			AND NOW() BETWEEN p.date_available AND p.date_end
			AND p2s.store_id = '" . (int) $this->config->get('config_store_id') . "'";
			$sql .= " GROUP BY p.record_id";
			$sort_data = array(
				'rating',
				'comments',
				'popular',
				'latest',
				'sort',
				'rand'
			);
			if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
				if ($data['sort'] == 'rating') {
					$data['sort'] = 'rating';
				}
				if ($data['sort'] == 'comments') {
					$data['sort'] = 'comments';
				}
				if ($data['sort'] == 'latest') {
					$data['sort'] = 'p.date_available';
				}
				if ($data['sort'] == 'sort') {
					$data['sort'] = 'p.sort_order';
				}
				if ($data['sort'] == 'popular') {
					$data['sort'] = 'p.viewed';
				}
				if ($data['sort'] == 'rand') {
					$data['sort'] = 'RAND()';
				}
				$sql .= " ORDER BY " . $data['sort'];
			} else {
				$sql .= " ORDER BY p.sort_order";
			}
			if (isset($data['order']) && (strtoupper($data['order']) == 'DESC')) {
				if ($data['sort'] != 'rand') {
					$sql .= " DESC";
				}
			} else {
				if ($data['sort'] != 'rand') {
					$sql .= " ASC";
				}
			}
			if (isset($data['start']['start'])) {
				$data['start'] = $data['start']['start'];
			} else {
				$data['start'] = 0;
			}
			if (isset($data['start']) || isset($data['limit'])) {
				if ($data['start'] < 0) {
					$data['start'] = 0;
				}
				if ($data['limit'] < 1) {
					$data['limit'] = 20;
				}
				if ($data['sort'] != 'rand') {
					$sql .= " LIMIT " . (int) $data['start'] . "," . (int) $data['limit'];
				} else {
					$sql .= " LIMIT " . (int) $data['limit'];
				}
			}
			$record_data = array();
			$total       = false;
			$query       = $this->db->query($sql);
			$query_total = $this->db->query("SELECT FOUND_ROWS() as total");
			$total       = $query_total->row['total'];
			foreach ($query->rows as $result) {
				$record_data[$result['record_id']] = $this->getRecord($result['record_id']);
				if (count($record_data[$result['record_id']]) > 0) {
					$record_data[$result['record_id']]['total'] = $total;
				}
			}
			$this->cache->set($cache_file, $record_data);
		}
		unset($ascp_settings);
		return $record_data;
	}
	public function getFoundRows()
	{
		$query = $this->db->query("SELECT FOUND_ROWS() as total");
		$total = $query->row['total'];
		return $total;
	}
}
}
