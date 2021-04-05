<?php

namespace DI\routes;

use DateTime;
use DI\router\Context;
use DI\decorators\Route;
use DI\decorators\Scripts;
use DI\decorators\Stylesheets;

#[Route('/debug')]
#[Scripts(['https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/js/bootstrap.bundle.min.js'])]
#[Stylesheets(['https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/css/bootstrap.min.css'])]
class Debug {
	public function get(Context $context) {
		$debug_times = json_decode($context->session('debug_time'), true);
		$tbody = '';
		foreach ($debug_times as $class => $method_detail) {
			foreach ($method_detail as $method_name => $method_time) {
				$tbody .= <<<HTML
					<tr>
						<th colspan="3">{$class}::{$method_name}</th>
					</tr>
				HTML;

				foreach ($method_time as $time) {
					$start = DateTime::createFromFormat('U.u', $time['start'])->format('H:i:s:v');
					$end = DateTime::createFromFormat('U.u', $time['end'])->format('H:i:s:v');

					$diff = DateTime::createFromFormat('U.u', $time['end'])->format('v') - DateTime::createFromFormat('U.u', $time['start'])->format('v');

					$first_0 = $diff < 100 ? '0' : '';
					$second_0 = $diff < 10 ? '0' : '';

					if ($diff === 0) {
						$complete_diff = 'Instantané';
					} else {
						$complete_diff = "00:00:00:$first_0$second_0$diff";
					}

					$tbody .= <<<HTML
						<tr>
							<td>{$start}</td>
							<td>{$end}</td>
							<td>{$complete_diff}</td>
						</tr>
					HTML;
				}
			}
		}
		
		return <<<HTML
		<div class="container">
			<div class="row">
				<div class="col-4 mt-1">
					<h1>Debug Timing</h1>
				</div>
				<div class="col-8 mt-1 pt-3">
					<form action="/debug/reset" method="post">
						<button type="submit" name="del" class="btn btn-success">Vider</button>
					</form>
				</div>
			</div>
			<div class="row">
				<div class="col-12">
					<table class="table responsive-table">
						<thead>
							<tr>
								<th>start</th>
								<th>stop</th>
								<th>difference</th>
							</tr>
						</thead>
						<tbody>
							{$tbody}
						</tbody>
					</table>
				</div>
			</div>
		</div>
		HTML;
	}

	#[Route('/debug/reset', method: 'post')]
	public function post(Context $context) {
		$context->session(reset: true);

		header('Location: /debug');
	}
}