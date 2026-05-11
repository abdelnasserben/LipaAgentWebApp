// Mock data for Lipa Agent Web App

window.AGENT_PROFILE = {
  id: 'agt_01HXKZ9P2Q3R4S5T6U7V8W9X',
  externalRef: 'AGT-2024-00142',
  fullName: 'Moussa Bacar',
  phoneCountryCode: '269',
  phoneNumber: '3201456',
  zone: 'Moroni Centre',
  canSellCards: true,
  canDoCashIn: true,
  canDoCashOut: true,
  floatAlertThreshold: 50000,
  contractRef: 'CONT-2023-0088',
  kycLevel: 'LEVEL_2',
  status: 'ACTIVE',
  createdAt: '2023-06-15T08:00:00Z',
};

window.AGENT_BALANCE = {
  walletId: 'wlt_01HXKZ9P2Q3R4S5T6U7V8W9X',
  availableBalance: 284750,
  frozenBalance: 0,
  currency: 'KMF',
  walletStatus: 'ACTIVE',
  updatedAt: new Date().toISOString(),
};

window.AGENT_SUMMARY = {
  todayCashIn: 142000,
  todayCashOut: 87500,
  todayTransactions: 23,
  todayCommission: 3840,
  weeklyVolume: 1240000,
  weeklyTransactions: 148,
  monthlyCommission: 28600,
};

window.AGENT_TRANSACTIONS = [
  { id: 'txn_A001', type: 'DEPOSIT',       counterpartyName: 'Ali Hassan',       counterpartyPhone: '+269 321 8821', amount: 15000,  fee: 150,  commission: 150,  status: 'SETTLED', createdAt: '2026-05-04T09:14:00Z', reference: 'CI-2026050401' },
  { id: 'txn_A002', type: 'WITHDRAWAL',    counterpartyName: 'Fatouma Youssouf', counterpartyPhone: '+269 321 4490', amount: 8500,   fee: 85,   commission: 85,   status: 'SETTLED', createdAt: '2026-05-04T09:02:00Z', reference: 'CO-2026050401' },
  { id: 'txn_A003', type: 'DEPOSIT',       counterpartyName: 'Ibrahim Said',     counterpartyPhone: '+269 333 1122', amount: 25000,  fee: 250,  commission: 250,  status: 'SETTLED', createdAt: '2026-05-04T08:47:00Z', reference: 'CI-2026050400' },
  { id: 'txn_A004', type: 'WITHDRAWAL',    counterpartyName: 'Mariam Combo',     counterpartyPhone: '+269 344 5566', amount: 12000,  fee: 120,  commission: 120,  status: 'SETTLED', createdAt: '2026-05-04T08:33:00Z', reference: 'CO-2026050400' },
  { id: 'txn_A005', type: 'DEPOSIT',       counterpartyName: 'Omar Abdallah',    counterpartyPhone: '+269 355 7788', amount: 50000,  fee: 500,  commission: 500,  status: 'SETTLED', createdAt: '2026-05-04T08:20:00Z', reference: 'CI-2026050399' },
  { id: 'txn_A006', type: 'WITHDRAWAL',    counterpartyName: 'Zahara Said',      counterpartyPhone: '+269 312 9900', amount: 5000,   fee: 50,   commission: 50,   status: 'SETTLED', createdAt: '2026-05-03T17:55:00Z', reference: 'CO-2026050398' },
  { id: 'txn_A007', type: 'DEPOSIT',       counterpartyName: 'Hamid Abdou',      counterpartyPhone: '+269 322 1133', amount: 30000,  fee: 300,  commission: 300,  status: 'SETTLED', createdAt: '2026-05-03T17:30:00Z', reference: 'CI-2026050397' },
  { id: 'txn_A008', type: 'WITHDRAWAL',    counterpartyName: 'Noura Mohamed',    counterpartyPhone: '+269 311 2244', amount: 18000,  fee: 180,  commission: 180,  status: 'FAILED',  createdAt: '2026-05-03T16:48:00Z', reference: 'CO-2026050396' },
  { id: 'txn_A009', type: 'DEPOSIT',       counterpartyName: 'Youssouf Combo',   counterpartyPhone: '+269 341 3355', amount: 10000,  fee: 100,  commission: 100,  status: 'SETTLED', createdAt: '2026-05-03T16:15:00Z', reference: 'CI-2026050395' },
  { id: 'txn_A010', type: 'WITHDRAWAL',    counterpartyName: 'Aïcha Bacar',      counterpartyPhone: '+269 352 4466', amount: 22000,  fee: 220,  commission: 220,  status: 'SETTLED', createdAt: '2026-05-03T15:40:00Z', reference: 'CO-2026050394' },
  { id: 'txn_A011', type: 'DEPOSIT',       counterpartyName: 'Said Moussa',      counterpartyPhone: '+269 313 5577', amount: 7500,   fee: 75,   commission: 75,   status: 'SETTLED', createdAt: '2026-05-03T14:55:00Z', reference: 'CI-2026050393' },
  { id: 'txn_A012', type: 'DEPOSIT',       counterpartyName: 'Fatima Ali',       counterpartyPhone: '+269 323 6688', amount: 45000,  fee: 450,  commission: 450,  status: 'PENDING', createdAt: '2026-05-03T14:20:00Z', reference: 'CI-2026050392' },
  { id: 'txn_A013', type: 'WITHDRAWAL',    counterpartyName: 'Kamar Hassani',    counterpartyPhone: '+269 343 7799', amount: 9000,   fee: 90,   commission: 90,   status: 'SETTLED', createdAt: '2026-05-02T11:30:00Z', reference: 'CO-2026050391' },
  { id: 'txn_A014', type: 'DEPOSIT',       counterpartyName: 'Abdou Salim',      counterpartyPhone: '+269 353 8800', amount: 60000,  fee: 600,  commission: 600,  status: 'SETTLED', createdAt: '2026-05-02T10:45:00Z', reference: 'CI-2026050390' },
  { id: 'txn_A015', type: 'WITHDRAWAL',    counterpartyName: 'Naima Hadji',      counterpartyPhone: '+269 314 9911', amount: 14000,  fee: 140,  commission: 140,  status: 'SETTLED', createdAt: '2026-05-02T09:55:00Z', reference: 'CO-2026050389' },
];

window.AGENT_STATEMENTS = [
  { id: 'stmt_001', postedAt: '2026-05-04T09:14:00Z', type: 'DEPOSIT',     counterpartyRef: 'CI-2026050401', counterpartyName: 'Ali Hassan',       amount: 15000,  currency: 'KMF', balanceBefore: 269750, balanceAfter: 284750, status: 'SETTLED' },
  { id: 'stmt_002', postedAt: '2026-05-04T09:02:00Z', type: 'WITHDRAWAL',  counterpartyRef: 'CO-2026050401', counterpartyName: 'Fatouma Youssouf', amount: -8500,  currency: 'KMF', balanceBefore: 278250, balanceAfter: 269750, status: 'SETTLED' },
  { id: 'stmt_003', postedAt: '2026-05-04T08:47:00Z', type: 'DEPOSIT',     counterpartyRef: 'CI-2026050400', counterpartyName: 'Ibrahim Said',     amount: 25000,  currency: 'KMF', balanceBefore: 253250, balanceAfter: 278250, status: 'SETTLED' },
  { id: 'stmt_004', postedAt: '2026-05-04T08:33:00Z', type: 'WITHDRAWAL',  counterpartyRef: 'CO-2026050400', counterpartyName: 'Mariam Combo',     amount: -12000, currency: 'KMF', balanceBefore: 265250, balanceAfter: 253250, status: 'SETTLED' },
  { id: 'stmt_005', postedAt: '2026-05-04T08:20:00Z', type: 'DEPOSIT',     counterpartyRef: 'CI-2026050399', counterpartyName: 'Omar Abdallah',    amount: 50000,  currency: 'KMF', balanceBefore: 215250, balanceAfter: 265250, status: 'SETTLED' },
  { id: 'stmt_006', postedAt: '2026-05-03T17:55:00Z', type: 'WITHDRAWAL',  counterpartyRef: 'CO-2026050398', counterpartyName: 'Zahara Said',      amount: -5000,  currency: 'KMF', balanceBefore: 220250, balanceAfter: 215250, status: 'SETTLED' },
  { id: 'stmt_007', postedAt: '2026-05-03T17:30:00Z', type: 'DEPOSIT',     counterpartyRef: 'CI-2026050397', counterpartyName: 'Hamid Abdou',      amount: 30000,  currency: 'KMF', balanceBefore: 190250, balanceAfter: 220250, status: 'SETTLED' },
  { id: 'stmt_008', postedAt: '2026-05-03T16:48:00Z', type: 'WITHDRAWAL',  counterpartyRef: 'CO-2026050396', counterpartyName: 'Noura Mohamed',    amount: -18000, currency: 'KMF', balanceBefore: 208250, balanceAfter: 190250, status: 'FAILED'  },
  { id: 'stmt_009', postedAt: '2026-05-03T16:15:00Z', type: 'DEPOSIT',     counterpartyRef: 'CI-2026050395', counterpartyName: 'Youssouf Combo',   amount: 10000,  currency: 'KMF', balanceBefore: 198250, balanceAfter: 208250, status: 'SETTLED' },
  { id: 'stmt_010', postedAt: '2026-05-03T15:40:00Z', type: 'WITHDRAWAL',  counterpartyRef: 'CO-2026050394', counterpartyName: 'Aïcha Bacar',      amount: -22000, currency: 'KMF', balanceBefore: 220250, balanceAfter: 198250, status: 'SETTLED' },
  { id: 'stmt_011', postedAt: '2026-05-03T14:55:00Z', type: 'DEPOSIT',     counterpartyRef: 'CI-2026050393', counterpartyName: 'Said Moussa',      amount: 7500,   currency: 'KMF', balanceBefore: 212750, balanceAfter: 220250, status: 'SETTLED' },
  { id: 'stmt_012', postedAt: '2026-05-03T14:20:00Z', type: 'DEPOSIT',     counterpartyRef: 'CI-2026050392', counterpartyName: 'Fatima Ali',       amount: 45000,  currency: 'KMF', balanceBefore: 167750, balanceAfter: 212750, status: 'PENDING' },
];

window.AGENT_COMMISSION = {
  today: 3840,
  thisWeek: 19200,
  thisMonth: 28600,
  lastMonth: 31400,
  totalEarned: 284600,
  pendingPayout: 28600,
  lastPayout: { amount: 31400, date: '2026-04-30T10:00:00Z', ref: 'PAY-2026-0430' },
  breakdown: [
    { label: 'Cash-In (CI)', count: 87,  volume: 742000,  commission: 14840 },
    { label: 'Cash-Out (CO)', count: 61,  volume: 498000,  commission: 13760 },
  ],
  history: [
    { month: 'May 2026',  amount: 28600, status: 'PENDING' },
    { month: 'Apr 2026',  amount: 31400, status: 'SETTLED' },
    { month: 'Mar 2026',  amount: 29800, status: 'SETTLED' },
    { month: 'Feb 2026',  amount: 26200, status: 'SETTLED' },
    { month: 'Jan 2026',  amount: 33100, status: 'SETTLED' },
    { month: 'Dec 2025',  amount: 30900, status: 'SETTLED' },
  ],
};

// Mock customer lookup results
window.AGENT_CUSTOMER_LOOKUP = {
  '3201234': {
    customerId: 'cust_01HXK001',
    fullName: 'Ali Hassan Abdou',
    phoneCountryCode: '269',
    phoneNumber: '3201234',
    status: 'ACTIVE',
    kycLevel: 'KYC_BASIC',
  },
  '3219876': {
    customerId: 'cust_01HXK002',
    fullName: 'Fatouma Youssouf Said',
    phoneCountryCode: '269',
    phoneNumber: '3219876',
    status: 'ACTIVE',
    kycLevel: 'KYC_VERIFIED',
  },
  '3445566': {
    customerId: 'cust_01HXK003',
    fullName: 'Omar Abdallah Combo',
    phoneCountryCode: '269',
    phoneNumber: '3445566',
    status: 'SUSPENDED',
    kycLevel: 'KYC_BASIC',
  },
};

// Mock merchants for cash-out
window.AGENT_MERCHANTS = {
  'MRCH-001': {
    merchantId: 'mrch_01HXK001',
    externalRef: 'MRCH-001',
    businessName: 'Épicerie Al Baraka',
    phoneCountryCode: '269',
    phoneNumber: '3301001',
    status: 'ACTIVE',
    kycLevel: 'KYC_VERIFIED',
  },
  'MRCH-002': {
    merchantId: 'mrch_01HXK002',
    externalRef: 'MRCH-002',
    businessName: 'Pharmacie Centrale',
    phoneCountryCode: '269',
    phoneNumber: '3302002',
    status: 'ACTIVE',
    kycLevel: 'KYC_ENHANCED',
  },
  'MRCH-003': {
    merchantId: 'mrch_01HXK003',
    externalRef: 'MRCH-003',
    businessName: 'Boutique Mode Moroni',
    phoneCountryCode: '269',
    phoneNumber: '3303003',
    status: 'SUSPENDED',
    kycLevel: 'KYC_BASIC',
  },
};

// Mock card stock
window.AGENT_CARD_STOCK = [
  { id: 'stk_001', nfcUid: '04AABB01CCDDE0', internalCardNumber: 'CARD-00421', batchRef: 'BATCH-2026-04', producedAt: '2026-04-01', assignedAt: '2026-04-15T08:00:00Z', status: 'ASSIGNED_TO_AGENT' },
  { id: 'stk_002', nfcUid: '04AABB02CCDDE1', internalCardNumber: 'CARD-00422', batchRef: 'BATCH-2026-04', producedAt: '2026-04-01', assignedAt: '2026-04-15T08:00:00Z', status: 'ASSIGNED_TO_AGENT' },
  { id: 'stk_003', nfcUid: '04AABB03CCDDE2', internalCardNumber: 'CARD-00423', batchRef: 'BATCH-2026-04', producedAt: '2026-04-01', assignedAt: '2026-04-15T08:00:00Z', status: 'ASSIGNED_TO_AGENT' },
  { id: 'stk_004', nfcUid: '04AABB04CCDDE3', internalCardNumber: 'CARD-00424', batchRef: 'BATCH-2026-04', producedAt: '2026-04-01', assignedAt: '2026-04-15T08:00:00Z', status: 'ASSIGNED_TO_AGENT' },
  { id: 'stk_005', nfcUid: '04AABB05CCDDE4', internalCardNumber: 'CARD-00425', batchRef: 'BATCH-2026-04', producedAt: '2026-04-01', assignedAt: '2026-04-15T08:00:00Z', status: 'ASSIGNED_TO_AGENT' },
];

window.AGENT_LIMITS = {
  float: {
    label: 'Float Balance',
    min: 10000,
    max: 500000,
    current: 284750,
    alert: 50000,
    currency: 'KMF',
  },
  cashIn: {
    label: 'Cash-In per Transaction',
    daily: { limit: 200000, used: 142000 },
    weekly: { limit: 800000, used: 387000 },
    monthly: { limit: 3000000, used: 1240000 },
    currency: 'KMF',
  },
  cashOut: {
    label: 'Cash-Out per Transaction',
    daily: { limit: 150000, used: 87500 },
    weekly: { limit: 600000, used: 280000 },
    monthly: { limit: 2500000, used: 980000 },
    currency: 'KMF',
  },
};
