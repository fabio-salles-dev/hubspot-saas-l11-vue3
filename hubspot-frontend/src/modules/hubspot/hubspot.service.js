// src/modules/hubspot/hubspot.service.js

import api from "../../services/api";

// ============================================================
// STATUS
// ============================================================

export const getHubspotStatus = () => {
  return api.get("/hubspot/status");
};

// ============================================================
// OVERVIEW
// ============================================================

export const getHubspotOverview = () => {
  return api.get("/hubspot/overview-live");
};

// ============================================================
// HISTORY
// ============================================================

export const getHubspotHistory = () => {
  return api.get("/hubspot/history");
};

// ============================================================
// DEAL SUMMARY
// ============================================================

export const getHubspotDealSummary = () => {
  return api.get("/hubspot/deals/summary");
};

// ============================================================
// IMPORT CONTACTS
// ============================================================

export const importHubspotContacts = () => {
  return api.post("/hubspot/import");
};

// ============================================================
// DISCONNECT
// ============================================================

export const disconnectHubspot = () => {
  return api.post("/hubspot/disconnect");
};

// ============================================================
// CONNECT
// ============================================================

export const getHubspotConnectUrl = () => {
  return "http://localhost:8000/api/hubspot/redirect";
};