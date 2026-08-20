<?php
/**
 * Desire Travel - Boarding Pass & Ticket HTML Template Generator
 */

require_once __DIR__ . '/lang.php';

function renderTicketHtml(array $booking): string {
    $statusColor = $booking['booking_status'] === 'Confirmed' ? '#10b981' : '#ef4444';
    $statusBg = $booking['booking_status'] === 'Confirmed' ? '#ecfdf5' : '#fef2f2';
    
    $qrData = urlencode("TICKET:" . $booking['ticket_number'] . "|PASSENGER:" . $booking['customer_name'] . "|SEATS:" . $booking['seat_numbers'] . "|FARE:" . $booking['total_fare']);
    $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=130x130&data=" . $qrData;

    ob_start();
    ?>
    <div class="ticket-boarding-pass" style="background:#ffffff;border:2px dashed #cbd5e1;border-radius:16px;overflow:hidden;box-shadow:0 10px 25px -5px rgba(0,0,0,0.1);max-width:850px;margin:15px auto;font-family:'Segoe UI',Roboto,Helvetica,sans-serif;color:#1e293b;">
        <!-- Header -->
        <div style="background:linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);color:#ffffff;padding:20px 28px;display:flex;justify-content:space-between;align-items:center;border-bottom:3px solid #f59e0b;">
            <div style="display:flex;align-items:center;gap:14px;">
                <div style="background:#ffffff;padding:6px 12px;border-radius:8px;display:flex;align-items:center;justify-content:center;">
                    <span style="font-size:24px;font-weight:900;color:#1e3c72;letter-spacing:-1px;">DT</span>
                </div>
                <div>
                    <h2 style="margin:0;font-size:22px;font-weight:800;letter-spacing:0.5px;color:#ffffff;"><?= htmlspecialchars(APP_NAME) ?></h2>
                    <p style="margin:2px 0 0;font-size:12px;opacity:0.85;letter-spacing:0.3px;color:#e2e8f0;"><?= htmlspecialchars(APP_TAGLINE) ?></p>
                </div>
            </div>
            <div style="text-align:right;">
                <div style="font-size:11px;text-transform:uppercase;letter-spacing:1px;opacity:0.8;color:#e2e8f0;">Official Boarding Pass</div>
                <div style="font-size:18px;font-weight:700;font-family:monospace;color:#fcd34d;letter-spacing:1px;"><?= htmlspecialchars($booking['ticket_number']) ?></div>
            </div>
        </div>

        <!-- Body -->
        <div style="display:flex;flex-wrap:wrap;padding:24px 28px;gap:20px;background:#ffffff;">
            <!-- Left Info Column -->
            <div style="flex:1 1 500px;">
                <!-- Route Highlight -->
                <div style="display:flex;align-items:center;justify-content:space-between;background:#f8fafc;padding:16px 20px;border-radius:12px;border:1px solid #e2e8f0;margin-bottom:20px;">
                    <div>
                        <div style="font-size:11px;color:#64748b;text-transform:uppercase;font-weight:600;">Origin</div>
                        <div style="font-size:20px;font-weight:800;color:#0f172a;"><?= htmlspecialchars($booking['start_point'] ?? 'Origin') ?></div>
                        <div style="font-size:13px;color:#2563eb;font-weight:600;"><?= date('h:i A', strtotime($booking['departure_time'])) ?></div>
                    </div>
                    <div style="text-align:center;padding:0 15px;">
                        <div style="font-size:11px;color:#94a3b8;font-weight:600;"><?= htmlspecialchars($booking['estimated_duration'] ?? 'Direct') ?></div>
                        <div style="display:flex;align-items:center;gap:6px;margin:4px 0;color:#f59e0b;">
                            <span style="height:2px;width:30px;background:#cbd5e1;display:inline-block;"></span>
                            <i class="bi bi-bus-front-fill" style="font-size:18px;color:#1e3c72;"></i>
                            <span style="height:2px;width:30px;background:#cbd5e1;display:inline-block;"></span>
                        </div>
                        <div style="font-size:10px;color:#64748b;font-weight:600;"><?= htmlspecialchars($booking['distance_km'] ?? '0') ?> KM</div>
                    </div>
                    <div style="text-align:right;">
                        <div style="font-size:11px;color:#64748b;text-transform:uppercase;font-weight:600;">Destination</div>
                        <div style="font-size:20px;font-weight:800;color:#0f172a;"><?= htmlspecialchars($booking['end_point'] ?? 'Destination') ?></div>
                        <div style="font-size:13px;color:#059669;font-weight:600;"><?= date('h:i A', strtotime($booking['arrival_time'])) ?></div>
                    </div>
                </div>

                <!-- Passenger & Travel Info Grid -->
                <div style="display:grid;grid-template-columns:repeat(3, 1fr);gap:16px;margin-bottom:16px;">
                    <div>
                        <div style="font-size:11px;color:#64748b;text-transform:uppercase;font-weight:600;">Passenger</div>
                        <div style="font-size:14px;font-weight:700;color:#1e293b;"><?= htmlspecialchars($booking['customer_name']) ?></div>
                        <div style="font-size:11px;color:#64748b;"><?= htmlspecialchars($booking['customer_contact']) ?></div>
                    </div>
                    <div>
                        <div style="font-size:11px;color:#64748b;text-transform:uppercase;font-weight:600;">Travel Date</div>
                        <div style="font-size:14px;font-weight:700;color:#1e293b;"><?= date('D, d M Y', strtotime($booking['travel_date'])) ?></div>
                    </div>
                    <div>
                        <div style="font-size:11px;color:#64748b;text-transform:uppercase;font-weight:600;">Bus & Type</div>
                        <div style="font-size:14px;font-weight:700;color:#1e293b;"><?= htmlspecialchars($booking['bus_name']) ?></div>
                        <div style="font-size:11px;color:#64748b;"><?= htmlspecialchars($booking['bus_number']) ?> (<?= htmlspecialchars($booking['bus_type']) ?>)</div>
                    </div>
                    <div>
                        <div style="font-size:11px;color:#64748b;text-transform:uppercase;font-weight:600;">Seat Number(s)</div>
                        <div style="font-size:18px;font-weight:900;color:#2563eb;letter-spacing:0.5px;"><?= htmlspecialchars($booking['seat_numbers']) ?></div>
                    </div>
                    <div>
                        <div style="font-size:11px;color:#64748b;text-transform:uppercase;font-weight:600;">Payment & Mode</div>
                        <div style="font-size:14px;font-weight:700;color:#059669;"><?= htmlspecialchars($booking['payment_status']) ?> (<?= htmlspecialchars($booking['payment_method']) ?>)</div>
                    </div>
                    <div>
                        <div style="font-size:11px;color:#64748b;text-transform:uppercase;font-weight:600;">Status</div>
                        <div>
                            <span style="display:inline-block;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:700;background:<?= $statusBg ?>;color:<?= $statusColor ?>;border:1px solid <?= $statusColor ?>;">
                                <?= htmlspecialchars($booking['booking_status']) ?>
                            </span>
                        </div>
                    </div>
                </div>

                <div style="font-size:11px;color:#94a3b8;border-top:1px dashed #e2e8f0;padding-top:10px;">
                    * Please report at least 15 minutes before scheduled departure. Valid Photo ID is mandatory during travel.
                </div>
            </div>

            <!-- Right QR & Total Column -->
            <div style="flex:0 0 200px;display:flex;flex-direction:column;justify-content:space-between;align-items:center;background:#f8fafc;padding:18px;border-radius:12px;border:1px solid #e2e8f0;text-align:center;">
                <div>
                    <img src="<?= $qrUrl ?>" alt="Ticket QR" style="width:115px;height:115px;border-radius:8px;border:1px solid #cbd5e1;background:#fff;padding:4px;" />
                    <div style="font-size:10px;color:#64748b;margin-top:6px;font-weight:600;">Scan to Verify</div>
                </div>
                <div style="width:100%;border-top:1px solid #e2e8f0;padding-top:12px;margin-top:10px;">
                    <div style="font-size:11px;color:#64748b;text-transform:uppercase;font-weight:600;">Total Fare Paid</div>
                    <div style="font-size:22px;font-weight:900;color:#0f172a;">₹<?= number_format((float)$booking['total_fare'], 2) ?></div>
                    <div style="font-size:10px;color:#64748b;"><?= (int)($booking['seat_count'] ?? 1) ?> Seat(s)</div>
                </div>
            </div>
        </div>

        <!-- Print Action Footer (Visible in Web Modal, Hidden during Print) -->
        <div class="ticket-print-actions" style="background:#f1f5f9;padding:12px 28px;display:flex;justify-content:space-between;align-items:center;border-top:1px solid #e2e8f0;">
            <span style="font-size:12px;color:#64748b;">Issued on <?= date('d M Y, h:i A', strtotime($booking['booking_date'] ?? date('Y-m-d H:i:s'))) ?></span>
            <div>
                <button onclick="window.print();" class="btn btn-sm btn-primary" style="padding:6px 16px;border-radius:8px;font-weight:600;cursor:pointer;">
                    <i class="bi bi-printer-fill me-1"></i> Print Boarding Pass
                </button>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
