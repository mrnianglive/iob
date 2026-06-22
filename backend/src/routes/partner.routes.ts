import { Router } from 'express';
import { PartnerController } from '../controllers/partner.controller';
import { authenticateToken, requireAdmin } from '../middleware/auth.middleware';

const router = Router();
const partnerController = new PartnerController();

// All routes require authentication
router.use(authenticateToken);

// Partners management
router.get('/', partnerController.getPartners);
router.post('/', requireAdmin, partnerController.createPartner);
router.get('/:id', partnerController.getPartnerById);
router.put('/:id', requireAdmin, partnerController.updatePartner);

// Partner relationships
router.get('/:id/products', partnerController.getPartnerProducts);
router.get('/:id/agencies', partnerController.getPartnerAgencies);
router.get('/:id/statistics', partnerController.getPartnerStatistics);

export default router;