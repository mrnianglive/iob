#!/usr/bin/env node

/**
 * Script de test pour vérifier la documentation Swagger
 * Usage: node test_swagger_docs.js
 */

const https = require('https');
const http = require('http');

const API_BASE_URL = process.env.API_URL || 'http://localhost:3001';
const API_PREFIX = '/partner-api';

console.log('🧪 Test de la Documentation API IOB Partner');
console.log('=' .repeat(50));

async function makeRequest(url) {
  return new Promise((resolve, reject) => {
    const client = url.startsWith('https') ? https : http;
    
    client.get(url, (res) => {
      let data = '';
      
      res.on('data', (chunk) => {
        data += chunk;
      });
      
      res.on('end', () => {
        resolve({
          statusCode: res.statusCode,
          headers: res.headers,
          body: data
        });
      });
    }).on('error', (err) => {
      reject(err);
    });
  });
}

async function testEndpoint(name, endpoint, expectedStatus = 200) {
  try {
    console.log(`📍 Test: ${name}`);
    console.log(`   URL: ${API_BASE_URL}${endpoint}`);
    
    const response = await makeRequest(`${API_BASE_URL}${endpoint}`);
    
    const status = response.statusCode === expectedStatus ? '✅' : '❌';
    console.log(`   Status: ${status} ${response.statusCode} (attendu: ${expectedStatus})`);
    
    if (response.headers['content-type']) {
      console.log(`   Content-Type: ${response.headers['content-type']}`);
    }
    
    if (response.body.length > 0) {
      const bodyPreview = response.body.length > 100 
        ? response.body.substring(0, 100) + '...' 
        : response.body;
      console.log(`   Body: ${bodyPreview.replace(/\n/g, ' ')}`);
    }
    
    console.log('');
    return response.statusCode === expectedStatus;
  } catch (error) {
    console.log(`❌ Erreur: ${error.message}`);
    console.log('');
    return false;
  }
}

async function runTests() {
  const tests = [
    {
      name: 'Health Check',
      endpoint: '/health',
      expectedStatus: 200
    },
    {
      name: 'Documentation Swagger UI',
      endpoint: '/docs',
      expectedStatus: 200
    },
    {
      name: 'OpenAPI JSON Spec',
      endpoint: '/docs.json',
      expectedStatus: 200
    },
    {
      name: 'API Base Path',
      endpoint: API_PREFIX,
      expectedStatus: 404 // Normal, pas de route à la racine
    },
    {
      name: 'Login Endpoint (sans auth)',
      endpoint: `${API_PREFIX}/auth/login`,
      expectedStatus: 400 // Bad request car pas de body
    },
    {
      name: 'Dashboard Stats (sans auth)',
      endpoint: `${API_PREFIX}/dashboard/stats`,
      expectedStatus: 401 // Unauthorized
    }
  ];

  console.log(`🚀 Démarrage des tests sur ${API_BASE_URL}`);
  console.log('');

  let passed = 0;
  let total = tests.length;

  for (const test of tests) {
    const success = await testEndpoint(test.name, test.endpoint, test.expectedStatus);
    if (success) passed++;
    
    // Petite pause entre les tests
    await new Promise(resolve => setTimeout(resolve, 500));
  }

  console.log('📊 Résultats des Tests');
  console.log('=' .repeat(30));
  console.log(`✅ Réussis: ${passed}/${total}`);
  console.log(`❌ Échoués: ${total - passed}/${total}`);
  
  if (passed === total) {
    console.log('');
    console.log('🎉 Tous les tests sont passés !');
    console.log('');
    console.log('📖 Documentation disponible sur :');
    console.log(`   Swagger UI: ${API_BASE_URL}/docs`);
    console.log(`   OpenAPI JSON: ${API_BASE_URL}/docs.json`);
  } else {
    console.log('');
    console.log('⚠️  Certains tests ont échoué. Vérifiez que le serveur est démarré.');
    process.exit(1);
  }
}

// Fonction pour vérifier si le serveur répond
async function waitForServer(maxAttempts = 10) {
  console.log('⏳ Attente du démarrage du serveur...');
  
  for (let i = 0; i < maxAttempts; i++) {
    try {
      await makeRequest(`${API_BASE_URL}/health`);
      console.log('✅ Serveur disponible !');
      console.log('');
      return true;
    } catch (error) {
      console.log(`   Tentative ${i + 1}/${maxAttempts}...`);
      await new Promise(resolve => setTimeout(resolve, 2000));
    }
  }
  
  console.log('❌ Impossible de joindre le serveur');
  return false;
}

// Script principal
async function main() {
  const serverReady = await waitForServer();
  
  if (!serverReady) {
    console.log('');
    console.log('💡 Pour démarrer le serveur :');
    console.log('   cd partner-api && npm run dev');
    process.exit(1);
  }
  
  await runTests();
}

// Exécution si appelé directement
if (require.main === module) {
  main().catch(console.error);
}

module.exports = { testEndpoint, makeRequest };