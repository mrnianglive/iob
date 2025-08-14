import { PrismaClient } from '@prisma/client';
import ExcelJS from 'exceljs';
import { PDFDocument, rgb, StandardFonts } from 'pdf-lib';
import { ExportOptions } from '@/types/partner';
import { logger } from '@/utils/logger';

export class ExportService {
  constructor(private prisma: PrismaClient) {}

  /**
   * Exporter les opérations dans le format demandé
   */
  async exportOperations(
    operations: any[],
    options: { format: 'pdf' | 'excel' | 'csv'; columns?: string[]; template?: string }
  ): Promise<{ buffer: Buffer; filename: string }> {
    const { format, columns, template } = options;

    switch (format) {
      case 'pdf':
        return this.exportToPDF(operations, columns);
      case 'excel':
        return this.exportToExcel(operations, columns);
      case 'csv':
        return this.exportToCSV(operations, columns);
      default:
        throw new Error(`Unsupported export format: ${format}`);
    }
  }

  /**
   * Export en PDF
   */
  private async exportToPDF(operations: any[], columns?: string[]): Promise<{ buffer: Buffer; filename: string }> {
    const pdfDoc = await PDFDocument.create();
    const font = await pdfDoc.embedFont(StandardFonts.Helvetica);
    const boldFont = await pdfDoc.embedFont(StandardFonts.HelveticaBold);
    
    let page = pdfDoc.addPage([595, 842]); // A4 format
    const { width, height } = page.getSize();
    
    let yPosition = height - 50;
    const lineHeight = 20;
    const margin = 50;

    // Titre
    page.drawText('Rapport des Opérations IOB', {
      x: margin,
      y: yPosition,
      size: 18,
      font: boldFont,
      color: rgb(0, 0, 0),
    });
    yPosition -= 30;

    // Date de génération
    page.drawText(`Généré le: ${new Date().toLocaleDateString('fr-FR')}`, {
      x: margin,
      y: yPosition,
      size: 10,
      font,
      color: rgb(0.5, 0.5, 0.5),
    });
    yPosition -= 30;

    // Statistiques générales
    const totalAmount = operations.reduce((sum, op) => sum + Number(op.Amount), 0);
    const totalCommissions = operations.reduce((sum, op) => sum + Number(op.Commission), 0);

    page.drawText(`Nombre total d'opérations: ${operations.length}`, {
      x: margin,
      y: yPosition,
      size: 12,
      font: boldFont,
    });
    yPosition -= lineHeight;

    page.drawText(`Montant total: ${totalAmount.toLocaleString('fr-FR', { style: 'currency', currency: 'EUR' })}`, {
      x: margin,
      y: yPosition,
      size: 12,
      font: boldFont,
    });
    yPosition -= lineHeight;

    page.drawText(`Commissions totales: ${totalCommissions.toLocaleString('fr-FR', { style: 'currency', currency: 'EUR' })}`, {
      x: margin,
      y: yPosition,
      size: 12,
      font: boldFont,
    });
    yPosition -= 40;

    // En-têtes de colonnes
    const defaultColumns = ['Reference', 'ClientName', 'Amount', 'Commission', 'Status', 'Insert_Time'];
    const activeColumns = columns || defaultColumns;
    
    const columnWidth = (width - 2 * margin) / activeColumns.length;
    let xPosition = margin;

    // Dessiner les en-têtes
    activeColumns.forEach(column => {
      page.drawText(this.getColumnTitle(column), {
        x: xPosition,
        y: yPosition,
        size: 10,
        font: boldFont,
      });
      xPosition += columnWidth;
    });
    yPosition -= lineHeight;

    // Ligne de séparation
    page.drawLine({
      start: { x: margin, y: yPosition + 5 },
      end: { x: width - margin, y: yPosition + 5 },
      thickness: 1,
      color: rgb(0, 0, 0),
    });
    yPosition -= 10;

    // Données des opérations
    for (const operation of operations.slice(0, 30)) { // Limiter à 30 opérations par page
      if (yPosition < 100) {
        // Nouvelle page si nécessaire
        page = pdfDoc.addPage([595, 842]);
        yPosition = height - 50;
      }

      xPosition = margin;
      activeColumns.forEach(column => {
        const value = this.formatCellValue(operation, column);
        page.drawText(value, {
          x: xPosition,
          y: yPosition,
          size: 9,
          font,
        });
        xPosition += columnWidth;
      });
      yPosition -= lineHeight;
    }

    const pdfBytes = await pdfDoc.save();
    const filename = `operations_${new Date().toISOString().split('T')[0]}.pdf`;

    return {
      buffer: Buffer.from(pdfBytes),
      filename
    };
  }

  /**
   * Export en Excel
   */
  private async exportToExcel(operations: any[], columns?: string[]): Promise<{ buffer: Buffer; filename: string }> {
    const workbook = new ExcelJS.Workbook();
    const worksheet = workbook.addWorksheet('Opérations');

    // Configuration du style
    const headerStyle = {
      font: { bold: true, color: { argb: 'FFFFFF' } },
      fill: { type: 'pattern', pattern: 'solid', fgColor: { argb: '366092' } },
      alignment: { horizontal: 'center' },
      border: {
        top: { style: 'thin' },
        left: { style: 'thin' },
        bottom: { style: 'thin' },
        right: { style: 'thin' }
      }
    };

    // Colonnes par défaut
    const defaultColumns = [
      { key: 'Reference', header: 'Référence' },
      { key: 'ClientName', header: 'Client' },
      { key: 'Amount', header: 'Montant' },
      { key: 'Commission', header: 'Commission' },
      { key: 'Status', header: 'Statut' },
      { key: 'Insert_Time', header: 'Date de création' },
      { key: 'cashRegister.agency.NameAgence', header: 'Agence' },
      { key: 'product.NameProduit', header: 'Produit' }
    ];

    // Filtrer les colonnes si spécifiées
    const activeColumns = columns 
      ? defaultColumns.filter(col => columns.includes(col.key))
      : defaultColumns;

    worksheet.columns = activeColumns.map(col => ({
      header: col.header,
      key: col.key,
      width: this.getColumnWidth(col.key)
    }));

    // Appliquer le style aux en-têtes
    worksheet.getRow(1).eachCell((cell) => {
      cell.style = headerStyle as any;
    });

    // Ajouter les données
    operations.forEach(operation => {
      const rowData: any = {};
      activeColumns.forEach(col => {
        rowData[col.key] = this.getNestedValue(operation, col.key);
      });
      worksheet.addRow(rowData);
    });

    // Formatage des colonnes numériques
    worksheet.getColumn('Amount').numFmt = '#,##0.00';
    worksheet.getColumn('Commission').numFmt = '#,##0.00';

    // Formatage des dates
    worksheet.getColumn('Insert_Time').numFmt = 'dd/mm/yyyy hh:mm';

    // Auto-fit des colonnes
    worksheet.columns.forEach(column => {
      if (column.eachCell) {
        let maxLength = 0;
        column.eachCell({ includeEmpty: true }, (cell) => {
          const columnLength = cell.value ? cell.value.toString().length : 10;
          if (columnLength > maxLength) {
            maxLength = columnLength;
          }
        });
        column.width = maxLength < 10 ? 10 : maxLength + 2;
      }
    });

    // Ajout d'un résumé en fin de tableau
    const summaryRowIndex = operations.length + 3;
    const totalAmount = operations.reduce((sum, op) => sum + Number(op.Amount), 0);
    const totalCommissions = operations.reduce((sum, op) => sum + Number(op.Commission), 0);

    worksheet.getCell(`A${summaryRowIndex}`).value = 'TOTAL';
    worksheet.getCell(`A${summaryRowIndex}`).font = { bold: true };
    worksheet.getCell(`C${summaryRowIndex}`).value = totalAmount;
    worksheet.getCell(`D${summaryRowIndex}`).value = totalCommissions;

    const buffer = await workbook.xlsx.writeBuffer();
    const filename = `operations_${new Date().toISOString().split('T')[0]}.xlsx`;

    return {
      buffer: Buffer.from(buffer),
      filename
    };
  }

  /**
   * Export en CSV
   */
  private async exportToCSV(operations: any[], columns?: string[]): Promise<{ buffer: Buffer; filename: string }> {
    const defaultColumns = ['Reference', 'ClientName', 'Amount', 'Commission', 'Status', 'Insert_Time'];
    const activeColumns = columns || defaultColumns;

    // En-têtes
    const headers = activeColumns.map(col => this.getColumnTitle(col));
    let csvContent = headers.join(';') + '\n';

    // Données
    operations.forEach(operation => {
      const row = activeColumns.map(column => {
        const value = this.formatCellValue(operation, column);
        // Échapper les guillemets et entourer de guillemets si nécessaire
        return value.includes(';') || value.includes('"') || value.includes('\n')
          ? `"${value.replace(/"/g, '""')}"`
          : value;
      });
      csvContent += row.join(';') + '\n';
    });

    const filename = `operations_${new Date().toISOString().split('T')[0]}.csv`;

    return {
      buffer: Buffer.from(csvContent, 'utf-8'),
      filename
    };
  }

  // ===== MÉTHODES UTILITAIRES =====

  private getColumnTitle(column: string): string {
    const titles: { [key: string]: string } = {
      'Reference': 'Référence',
      'ClientName': 'Client',
      'Amount': 'Montant',
      'Commission': 'Commission',
      'Status': 'Statut',
      'Insert_Time': 'Date de création',
      'BenefName': 'Bénéficiaire',
      'BenefPhone': 'Téléphone bénéficiaire',
      'BenefCountry': 'Pays bénéficiaire'
    };
    return titles[column] || column;
  }

  private getColumnWidth(column: string): number {
    const widths: { [key: string]: number } = {
      'Reference': 15,
      'ClientName': 25,
      'Amount': 12,
      'Commission': 12,
      'Status': 12,
      'Insert_Time': 18,
      'BenefName': 25,
      'BenefPhone': 15,
      'BenefCountry': 15
    };
    return widths[column] || 15;
  }

  private formatCellValue(operation: any, column: string): string {
    let value = this.getNestedValue(operation, column);

    if (value === null || value === undefined) {
      return '';
    }

    // Formatage spécifique selon le type de colonne
    switch (column) {
      case 'Amount':
      case 'Commission':
        return Number(value).toLocaleString('fr-FR', {
          minimumFractionDigits: 2,
          maximumFractionDigits: 2
        });
      case 'Insert_Time':
        return new Date(value).toLocaleString('fr-FR');
      case 'Status':
        const statusLabels: { [key: string]: string } = {
          'pending': 'En attente',
          'approved': 'Approuvé',
          'rejected': 'Rejeté',
          'cancelled': 'Annulé'
        };
        return statusLabels[value] || value;
      default:
        return value.toString();
    }
  }

  private getNestedValue(obj: any, path: string): any {
    return path.split('.').reduce((current, key) => {
      return current && current[key] !== undefined ? current[key] : null;
    }, obj);
  }
}