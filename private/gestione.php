<?php 
session_start();
if (!isset($_SESSION['utente_id'])) {
    header('Location: ../public/accesso.php');
    exit;
}
// Timeout di 5 minuti (300 secondi)
$timeout = 300;
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout)) {
    session_unset();
    session_destroy();
    header('Location: ../public/accesso.php?timeout=1');
    exit;
}
$_SESSION['last_activity'] = time();
echo "<script>
    setTimeout(function() {
        window.location.href = '../public/accesso.php?timeout=1';
    }, " . ($timeout * 1000) . ");
</script>";
?>

<a href="?logout=1" class="btn btn-danger float-end" 
style="background:#b71c1c;color:#fff;padding:0.7em 1.5em;border-radius:5px;text-decoration:none;">Logout</a>
<?php
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header('Location: ../public/home_cliente.php');
    exit;
}
?>
<?php
// Entry point for the car rental system
require_once __DIR__ . '/../src/bootstrap.php';
?><!DOCTYPE html></a>
	<a href="tipologie_noleggio.php" style="background:#27ae60;color:#fff;padding:0.7em 1.5em;border-radius:5px;text-decoration:none;">Tipologie</a>
	<a href="automezzi.php" style="background:#2980b9;color:#fff;padding:0.7em 1.5em;border-radius:5px;text-decoration:none;">Automezzi</a>
	<a href="autisti.php" style="background:#8e44ad;color:#fff;padding:0.7em 1.5em;border-radius:5px;text-decoration:none;">Autisti</a>
	<a href="clienti.php" style="background:#16a085;color:#fff;padding:0.7em 1.5em;border-radius:5px;text-decoration:none;">Clienti</a>
	<a href="fornitori.php" style="background:#34495e;color:#fff;padding:0.7em 1.5em;border-radius:5px;text-decoration:none;">Fornitori</a>
	<a href="inserisci_noleggio.php" style="background:#e67e22;color:#fff;padding:0.7em 1.5em;border-radius:5px;text-decoration:none;">Noleggio</a>
	<a href="fatture.php" style="background:#b71c1c;color:#fff;padding:0.7em 1.5em;border-radius:5px;text-decoration:none;">Elenco Fatture</a>
</div>
<html lang="it">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Noleggio Car Rental System</title>
	<style>
		#navbar-noleggio {
			position: sticky;
			top: 0;
			background: rgba(244,244,244,0.98);
			z-index: 1000;
			box-shadow: 0 2px 8px rgba(0,0,0,0.07);
			padding-top: 0.5em;
			padding-bottom: 0.5em;
			justify-content: flex-start;
			margin-top: 0;
		}
		body {
			font-family: Arial, sans-serif;
			background: #f4f4f4;
			margin: 0;
			padding: 0;
			display: block;
			height: auto;
			padding-top: 32px;
		}
		.welcome {
			background: rgba(255,255,255,0.92);
			padding: 2rem 3rem;
			border-radius: 10px;
			box-shadow: 0 2px 8px rgba(0,0,0,0.1);
			text-align: center;
		}
		h1 {
			color: #2c3e50;
		}
		p {
			color: #555;
		}

		/* Calendario FullCalendar */
		#calendar {
			max-width: 900px;
			margin: 2em auto;
			background: #fff;
			border-radius: 8px;
			box-shadow: 0 2px 8px rgba(0,0,0,0.08);
			padding: 1em;
		}
		</style>
		<link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css' rel='stylesheet' />
		<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js'></script>
	</head>
	   <body>
	<div style="display:flex;justify-content:center;align-items:center;margin-top:2em;gap:1.5em;">
		<div>
			<button id="prev-month" style="margin-right:1em;padding:0.5em 1.2em;">&larr; Mese precedente</button>
			<button id="oggi-month" style="margin-right:1em;padding:0.5em 1.2em;">Mese corrente</button>
			<button id="next-month" style="padding:0.5em 1.2em;">Mese successivo &rarr;</button>
		</div>
		<form method="get" style="margin:0;display:flex;align-items:center;gap:0.5em;">
			<label for="filtro_pagato" style="font-weight:bold;">pagati:</label>
			<select name="pagato" id="filtro_pagato" onchange="this.form.submit()" style="padding:0.4em 1em;font-size:1em;">
				<option value=""<?= !isset($_GET['pagato']) || $_GET['pagato']==='' ? ' selected' : '' ?>>Tutti</option>
				<option value="1"<?= isset($_GET['pagato']) && $_GET['pagato']==='1' ? ' selected' : '' ?>>Solo pagati</option>
				<option value="0"<?= isset($_GET['pagato']) && $_GET['pagato']==='0' ? ' selected' : '' ?>>Solo non pagati</option>
			</select>
		</form>
	</div>
	<div id="mese-anno" style="font-size:1.3em;font-weight:bold;margin-bottom:0.5em;text-align:center;"></div>
		<div id="timeline-container" style="max-width:100%;overflow-x:auto;margin:2em 0 0 0;"></div>
	<div id="dettagli-noleggio" style="min-width:320px;max-width:420px;padding:0;margin:2em 0 0 0;display:none;"></div>
		<script>
		// Timeline orizzontale dei noleggi (mese selezionato)
		let timelineYear, timelineMonth;
		function renderTimeline(year, month) {
			const mesi = ['Gennaio','Febbraio','Marzo','Aprile','Maggio','Giugno','Luglio','Agosto','Settembre','Ottobre','Novembre','Dicembre'];
			document.getElementById('mese-anno').textContent = mesi[month] + ' ' + year;
			const daysInMonth = new Date(year, month+1, 0).getDate();
			let html = '<table style="border-collapse:collapse;width:auto;min-width:900px;background:#fff;border-radius:8px;box-shadow:0 2px 8px rgba(0,0,0,0.08);">';
			html += '<thead><tr><th style="background:#eee;position:sticky;left:0;z-index:2;border:1px solid #ccc;">Automezzo</th>';
			for(let d=1;d<=daysInMonth;d++) {
				html += `<th style=\"background:#f8f8f8;width:32px;border:1px solid #ccc;\">${d}</th>`;
			}
			html += '</tr></thead><tbody id="timeline-body"></tbody></table>';
			document.getElementById('timeline-container').innerHTML = html;
			// Applica filtro pagato se presente
			let filtroPagato = '';
			const urlParams = new URLSearchParams(window.location.search);
			if(urlParams.has('pagato')) {
				filtroPagato = '&pagato=' + encodeURIComponent(urlParams.get('pagato'));
			}
			fetch('api_noleggi.php?' + (filtroPagato ? filtroPagato.substring(1) : '')).then(r=>r.json()).then(data=>{
				   // Raggruppa: ogni noleggio senza automezzo ha una riga distinta (key = 'senza_' + id), gli altri per automezzo_id
				   const mezziMap = {};
				   data.forEach(ev => {
					   let key;
					   if (ev.automezzo_id === null || ev.automezzo_id === undefined || ev.automezzo_id === '' || ev.mezzo_label === '(Senza automezzo)') {
						   key = 'senza_' + ev.id; // una riga per ogni noleggio senza automezzo
					   } else {
						   key = ev.automezzo_id;
					   }
					   if (!mezziMap[key]) {
						   mezziMap[key] = {
							   mezzo_label: ev.mezzo_label || ev.targa || '-',
							   targa: ev.targa || '',
							   noleggi: []
						   };
					   }
					   if (ev.id) {
						   mezziMap[key].noleggi.push(ev);
					   }
				   });

				let rows = '';
				Object.values(mezziMap).forEach(mezzo => {
					let row = `<tr><td style=\"background:#eee;position:sticky;left:0;z-index:1;white-space:nowrap;border:1px solid #ccc;\">`;
					row += mezzo.mezzo_label;
					if(mezzo.targa) row += '<br><small>'+mezzo.targa+'</small>';
					row += '</td>';
					for(let d=1;d<=daysInMonth;d++) {
						// Per ogni giorno, cerca se c'è un noleggio che copre quel giorno
						let cell = '';
						let found = false;
						let cellNoleggioId = null;
						mezzo.noleggi.forEach(ev => {
							if(ev.start && ev.end) {
								const start = new Date(ev.start);
								const end = new Date(ev.end);
								const day = new Date(year, month, d);
								if(day>=start && day<end) {
									cellNoleggioId = ev.id;
									found = true;
								}
							}
						});
						if(found && cellNoleggioId) {
							// Tutte le celle del periodo sono cliccabili
							cell = `<a href='#' class='noleggio-link' data-id='${cellNoleggioId}' style='display:block;width:100%;height:100%;background:#e67e22;color:#fff;text-align:center;font-weight:bold;text-decoration:none;border-radius:3px;'>●</a>`;
							row += `<td style=\"border:1px solid #ccc;padding:0;\">${cell}</td>`;
						} else {
							row += '<td style=\"border:1px solid #ccc;\"></td>';
						}
					}
					row += '</tr>';
					rows += row;
				});
				document.getElementById('timeline-body').innerHTML = rows || '<tr><td colspan="'+(daysInMonth+1)+'" style="text-align:center;">Nessun automezzo trovato</td></tr>';
				// Gestione click sui link
				document.querySelectorAll('.noleggio-link').forEach(link => {
					link.onclick = function(e) {
						e.preventDefault();
						caricaDettagliNoleggio(this.dataset.id);
					};
				});
			});
		}
		// Carica dettagli noleggio in home
		   function caricaDettagliNoleggio(id) {
			   fetch('api_noleggi.php?id='+id)
				   .then(r=>r.json())
				   .then(data=>{
					   let html = '';
					   if(data && data.id) {
						   const { Fattura, ...noleggioData } = data;
						   // Tabella dati noleggio
						   html += '<table style="width:100%;border-collapse:collapse;margin-bottom:1em;">';
						   html += '<tr>';
						   for(const k in noleggioData) {
							   if(k!=='id') html += `<th style=\"padding:0.5em 0.7em;background:#eee;\">${k.replace(/_/g,' ')}</th>`;
						   }
						   html += '</tr><tr>';
						   for(const k in noleggioData) {
							   if(k!=='id') {
								   let value = (noleggioData[k] === null || noleggioData[k] === undefined || noleggioData[k] === '') ? '-' : noleggioData[k];
								   html += `<td style=\"padding:0.5em 0.7em;\">${value}</td>`;
							   }
						   }
						   html += '</tr></table>';
						   // Tabella fattura (senza titolo) se presente
						   if (Fattura) {
							   html += '<table style="width:100%;border-collapse:collapse;margin-bottom:1em;">';
							   html += '<tr>';
							   for(const k in Fattura) {
								   html += `<th style=\"padding:0.4em 0.7em;background:#ffeaea;color:#c0392b;\">${k}</th>`;
							   }
							   html += '</tr><tr>';
							   for(const k in Fattura) {
								   let value = (Fattura[k] === null || Fattura[k] === undefined || Fattura[k] === '') ? '-' : Fattura[k];
								   html += `<td style=\"padding:0.4em 0.7em;\">${value}</td>`;
							   }
							   html += '</tr></table>';
						   }
						   html = `<div style=\"display:flex;align-items:center;gap:1em;margin-bottom:1em;\">` +
							   `<a href='inserisci_noleggio.php?id=${data.id}' style=\"display:inline-block;padding:0.6em 1.2em;background:#2980b9;color:#fff;border-radius:5px;text-decoration:none;font-weight:bold;\">Modifica</a>` +
							   `<a href='inserisci_fattura.php?noleggio_id=${data.id}' style=\"display:inline-block;padding:0.6em 1.2em;background:#c0392b;color:#fff;border-radius:5px;text-decoration:none;font-weight:bold;\">Aggiungi Fattura</a>` +
							   `<div style=\"flex:1;\"></div>` +
						   `</div>` + html;
					   } else {
						   html += '<em>Noleggio non trovato.</em>';
					   }
					   document.getElementById('dettagli-noleggio').innerHTML = html;
					   document.getElementById('dettagli-noleggio').style.display = 'block';
				   });
		   }
		document.addEventListener('DOMContentLoaded', function() {
			const today = new Date();
			timelineYear = today.getFullYear();
			timelineMonth = today.getMonth();
			renderTimeline(timelineYear, timelineMonth);
			document.getElementById('prev-month').onclick = function() {
				timelineMonth--;
				if(timelineMonth<0) { timelineMonth=11; timelineYear--; }
				renderTimeline(timelineYear, timelineMonth);
			};
			document.getElementById('next-month').onclick = function() {
				timelineMonth++;
				if(timelineMonth>11) { timelineMonth=0; timelineYear++; }
				renderTimeline(timelineYear, timelineMonth);
			};
			document.getElementById('oggi-month').onclick = function() {
				const now = new Date();
				timelineYear = now.getFullYear();
				timelineMonth = now.getMonth();
				renderTimeline(timelineYear, timelineMonth);
			};
		});
		   </script>
		   </body>
