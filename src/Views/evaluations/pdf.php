<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Evaluación de Calidad #
        <?php echo $evaluation['id']; ?>
    </title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 12px;
            color: #333;
            line-height: 1.5;
        }

        .header {
            width: 100%;
            border-bottom: 2px solid #4f46e5;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }

        .header-table {
            width: 100%;
        }

        .logo-text {
            font-size: 24px;
            font-weight: bold;
            color: #4f46e5;
        }

        .report-title {
            text-align: right;
            font-size: 18px;
            color: #666;
        }

        .score-box {
            background-color: #4f46e5;
            color: white;
            padding: 20px;
            margin-bottom: 20px;
            text-align: center;
            border-radius: 8px;
        }

        .score-value {
            font-size: 40px;
            font-weight: bold;
            display: block;
        }

        .score-label {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
            opacity: 0.9;
        }

        .meta-table {
            width: 100%;
            margin-bottom: 30px;
            border-collapse: collapse;
        }

        .meta-table td {
            padding: 8px;
            border-bottom: 1px solid #eee;
        }

        .meta-label {
            font-weight: bold;
            color: #666;
            width: 150px;
            text-transform: uppercase;
            font-size: 10px;
        }

        .meta-value {
            font-weight: bold;
            font-size: 13px;
        }

        .items-header {
            background-color: #f3f4f6;
            padding: 10px;
            margin-bottom: 10px;
            font-weight: bold;
            font-size: 14px;
            border-left: 4px solid #4f46e5;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        .items-table th {
            text-align: left;
            background-color: #f9fafb;
            padding: 10px;
            font-size: 10px;
            text-transform: uppercase;
            color: #6b7280;
            border-bottom: 1px solid #e5e7eb;
        }

        .items-table td {
            padding: 12px 10px;
            border-bottom: 1px solid #eee;
        }

        .item-label {
            font-weight: bold;
            font-size: 13px;
        }

        .item-weight {
            font-size: 10px;
            color: #666;
            background: #f3f4f6;
            padding: 2px 6px;
            border-radius: 4px;
            margin-left: 5px;
        }

        .status-pass {
            color: #059669;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 11px;
        }

        .status-fail {
            color: #dc2626;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 11px;
        }

        .comment-box {
            background-color: #fffbeb;
            border: 1px solid #fcd34d;
            padding: 8px;
            margin-top: 5px;
            font-style: italic;
            font-size: 11px;
            color: #92400e;
        }

        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 10px;
            color: #9ca3af;
            border-top: 1px solid #eee;
            padding-top: 20px;
        }
    </style>
</head>

<body>

    <div class="header">
        <table class="header-table">
            <tr>
                <td class="logo-text">Evallish BPO</td>
                <td class="report-title">Reporte de Calidad</td>
            </tr>
        </table>
    </div>

    <!-- Score Header -->
    <table width="100%">
        <tr>
            <td width="60%" valign="top">
                <table class="meta-table">
                    <tr>
                        <td class="meta-label">Agente</td>
                        <td class="meta-value">
                            <?php echo htmlspecialchars($evaluation['agent_name']); ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="meta-label">Campaña</td>
                        <td class="meta-value">
                            <?php echo htmlspecialchars($evaluation['campaign_name']); ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="meta-label">Formulario</td>
                        <td class="meta-value">
                            <?php echo htmlspecialchars($evaluation['form_title']); ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="meta-label">Evaluador (QA)</td>
                        <td class="meta-value">
                            <?php echo htmlspecialchars($evaluation['qa_name']); ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="meta-label">Fecha</td>
                        <td class="meta-value">
                            <?php echo date('d/m/Y H:i', strtotime($evaluation['created_at'])); ?>
                        </td>
                    </tr>
                </table>
            </td>
            <td width="5%"></td>
            <td width="35%" valign="top">
                <div class="score-box">
                    <span class="score-label">Calificación Final</span>
                    <span class="score-value">
                        <?php echo number_format($evaluation['percentage'], 1); ?>%
                    </span>
                </div>
            </td>
        </tr>
    </table>

    <div class="items-header">Detalle de Evaluación</div>

    <table class="items-table">
        <thead>
            <tr>
                <th width="50%">Ítem Evaluado</th>
                <th width="15%">Peso</th>
                <th width="20%">Puntaje</th>
                <th width="15%">Resultado</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($answers as $answer): ?>
                <tr>
                    <td>
                        <div class="item-label">
                            <?php echo htmlspecialchars($answer['field_label']); ?>
                        </div>
                        <?php if (!empty($answer['comment'])): ?>
                            <div class="comment-box">
                                "
                                <?php echo htmlspecialchars($answer['comment']); ?>"
                            </div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php echo number_format($answer['field_weight'], 0); ?>%
                    </td>
                    <td>
                        <?php if ($answer['field_type'] === 'score'): ?>
                            <?php echo number_format($answer['score_given'], 0); ?> / 100
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($answer['field_type'] === 'yes_no'): ?>
                            <?php if ($answer['score_given'] == 100): ?>
                                <span class="status-pass">CUMPLE</span>
                            <?php else: ?>
                                <span class="status-fail">NO CUMPLE</span>
                            <?php endif; ?>
                        <?php elseif ($answer['field_type'] === 'score'): ?>
                            <span style="font-weight:bold;">
                                <?php echo number_format($answer['score_given'], 0); ?>
                            </span>
                        <?php else: ?>
                            <?php echo htmlspecialchars($answer['score_given']); ?>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="items-header">Comentarios Generales</div>
    <div style="padding: 10px; background: #f9fafb; border: 1px solid #eee;">
        <?php echo !empty($evaluation['general_comments']) ? nl2br(htmlspecialchars($evaluation['general_comments'])) : 'Sin comentarios generales.'; ?>
    </div>

    <div class="footer">
        Generado por Sistema de Calidad Evallish &bull; Fecha de impresión:
        <?php echo date('d/m/Y H:i'); ?>
    </div>

</body>

</html>