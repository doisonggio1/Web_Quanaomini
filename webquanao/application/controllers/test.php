<?php
defined('BASEPATH') or exit('No direct script access allowed');

require_once FCPATH . 'vendor/autoload.php'; // Đường dẫn tới autoload Composer

use Google\Analytics\Data\V1beta\BetaAnalyticsDataClient;
use Google\Analytics\Data\V1beta\DateRange;
use Google\Analytics\Data\V1beta\Metric;

class Test extends MY_Controller
{
	public function index()
	{
		$KEY_FILE_PATH = APPPATH . 'third_party/ga4-key.json'; // đường dẫn tới file JSON key
		$property_id = '492315679'; // Mã GA4 property

		$client = new BetaAnalyticsDataClient([
			'credentials' => $KEY_FILE_PATH
		]);

		try {
			// ===================== THỐNG KÊ 7 NGÀY QUA =====================
			$responseWeek = $client->runReport([
				'property' => 'properties/' . $property_id,
				'dateRanges' => [
					new DateRange(['start_date' => '7daysAgo', 'end_date' => 'today'])
				],
				'metrics' => [
					new Metric(['name' => 'activeUsers']),
					new Metric(['name' => 'sessions']),
					new Metric(['name' => 'newUsers']),
					new Metric(['name' => 'engagedSessions']),
					new Metric(['name' => 'bounceRate']),
					new Metric(['name' => 'averageSessionDuration']),
				]
			]);

			$dataWeek = [];
			foreach ($responseWeek->getRows() as $row) {
				foreach ($row->getMetricValues() as $i => $metric) {
					$name = $responseWeek->getMetricHeaders()[$i]->getName();
					$dataWeek[$name] = $metric->getValue();
				}
			}

			// ===================== NGƯỜI DÙNG TRONG THÁNG HIỆN TẠI =====================
			$startOfMonth = date('Y-m-01');
			$today = date('Y-m-d');

			$responseMonth = $client->runReport([
				'property' => 'properties/' . $property_id,
				'dateRanges' => [
					new DateRange(['start_date' => $startOfMonth, 'end_date' => $today])
				],
				'metrics' => [
					new Metric(['name' => 'totalUsers']),
					new Metric(['name' => 'newUsers']), // 👈 Thêm dòng này
				]
			]);

			$dataMonth = [];
			foreach ($responseMonth->getRows() as $row) {
				foreach ($row->getMetricValues() as $i => $metric) {
					$name = $responseMonth->getMetricHeaders()[$i]->getName();
					$dataMonth[$name] = $metric->getValue();
				}
			}

			// ===================== GỬI DỮ LIỆU RA VIEW =====================
			$this->load->view('site/user/test', [
				'data' => $dataWeek,
				'dataMonth' => $dataMonth
			]);
		} catch (Exception $e) {
			echo 'Lỗi: ' . $e->getMessage();
		}
	}
}
