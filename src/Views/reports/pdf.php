<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title>Reporte de Calidad - Evallish BPO</title>
    <style>
        body {
            font-family: Helvetica, Arial, sans-serif;
            font-size: 12px;
            color: #1f2937;
            line-height: 1.4;
        }

        .header {
            border-bottom: 2px solid #4f46e5;
            padding-bottom: 12px;
            margin-bottom: 18px;
        }

        .title {
            font-size: 20px;
            font-weight: bold;
            color: #111827;
        }

        .subtitle {
            font-size: 11px;
            color: #6b7280;
        }

        .summary-grid {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .summary-grid td {
            border: 1px solid #e5e7eb;
            padding: 10px;
            vertical-align: top;
        }

        .summary-label {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #6b7280;
        }

        .summary-value {
            font-size: 18px;
            font-weight: bold;
            color: #111827;
        }

        .section-title {
            font-size: 13px;
            font-weight: bold;
            margin: 16px 0 8px;
            color: #111827;
        }

        table.report {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }

        table.report th,
        table.report td {
            border: 1px solid #e5e7eb;
            padding: 8px;
            font-size: 11px;
        }

        table.report th {
            background: #f3f4f6;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            font-size: 10px;
            color: #6b7280;
        }

        .footer {
            margin-top: 24px;
            font-size: 10px;
            color: #9ca3af;
            text-align: center;
            border-top: 1px solid #e5e7eb;
            padding-top: 12px;
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="title">Reporte de Calidad</div>
        <div class="subtitle">Evallish BPO • Generado el <?php echo date('d/m/Y H:i'); ?></div>
    </div>

    <table class="summary-grid">
        <tr>
            <td>
                <div class="summary-label">Evaluaciones</div>
                <div class="summary-value"><?php echo number_format($overallStats['total_evaluations'] ?? 0); ?></div>
            </td>
            <td>
                <div class="summary-label">Promedio global</div>
                <div class="summary-value"><?php echo number_format($overallStats['avg_score'] ?? 0, 1); ?>%</div>
            </td>
            <td>
                <div class="summary-label">Cumplimiento</div>
                <div class="summary-value"><?php echo number_format($overallStats['pass_rate'] ?? 0, 1); ?>%</div>
            </td>
            <td>
                <div class="summary-label">Mejor puntuación</div>
                <div class="summary-value"><?php echo number_format($overallStats['max_score'] ?? 0, 1); ?>%</div>
            </td>
        </tr>
    </table>

    <div class="section-title">Rendimiento por campaña</div>
    <table class="report">
        <thead>
            <tr>
                <th>Campaña</th>
                <th>Evaluaciones</th>
                <th>Promedio</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($campaignStats)): ?>
                <?php foreach ($campaignStats as $stat): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($stat['campaign_name']); ?></td>
                        <td><?php echo (int) $stat['total_evaluations']; ?></td>
                        <td><?php echo number_format($stat['avg_score'], 1); ?>%</td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="3">Sin datos de campaña.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="section-title">Top 10 agentes</div>
    <table class="report">
        <thead>
            <tr>
                <th>Agente</th>
                <th>Evaluaciones</th>
                <th>Promedio</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($topAgents)): ?>
                <?php foreach ($topAgents as $agent): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($agent['agent_name']); ?></td>
                        <td><?php echo (int) $agent['total_evaluations']; ?></td>
                        <td><?php echo number_format($agent['avg_score'], 1); ?>%</td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="3">Sin datos de agentes.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="section-title">QA por consistencia</div>
    <table class="report">
        <thead>
            <tr>
                <th>QA</th>
                <th>Evaluaciones</th>
                <th>Promedio</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($qaStats)): ?>
                <?php foreach ($qaStats as $qa): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($qa['qa_name']); ?></td>
                        <td><?php echo (int) $qa['total_evaluations']; ?></td>
                        <td><?php echo number_format($qa['avg_score'], 1); ?>%</td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="3">Sin datos de QA.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="footer">
        Sistema de Calidad Evallish • Reporte general de rendimiento
    </div>
</body>

</html>
