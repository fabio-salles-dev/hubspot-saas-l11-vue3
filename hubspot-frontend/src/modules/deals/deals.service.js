// src/modules/deals/deals.service.js

import api from "../../services/api";

// ============================================================
// LIST
// ============================================================

export const getDeals = () => {
  return api.get("/deals");
};

// ============================================================
// CREATE
// ============================================================

export const createDeal = (payload) => {
  return api.post("/deals", payload);
};

// ============================================================
// UPDATE
// ============================================================

export const updateDeal = (id, payload) => {
  return api.put(`/deals/${id}`, payload);
};

// ============================================================
// DELETE
// ============================================================

export const deleteDeal = (id) => {
  return api.delete(`/deals/${id}`);
};