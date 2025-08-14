import { Router } from 'express';
import { CountryController } from '../controllers/country.controller';
import { authenticateToken, requireAdmin } from '../middleware/auth.middleware';

const router = Router();
const countryController = new CountryController();

// All routes require authentication
router.use(authenticateToken);

// Countries management
router.get('/', countryController.getCountries);
router.get('/:id', countryController.getCountryById);
router.put('/:id', requireAdmin, countryController.updateCountry);

// Country relationships
router.get('/:id/agencies', countryController.getCountryAgencies);
router.get('/:id/partners', countryController.getCountryPartners);
router.get('/:id/statistics', countryController.getCountryStatistics);
router.get('/:id/users', requireAdmin, countryController.getCountryUsers);

export default router;