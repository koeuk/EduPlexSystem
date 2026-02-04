# Bakong Payment Integration Guide

## Overview

EduPlex integrates with **Bakong** (Cambodia's national payment system) using **KHQR** (Khmer QR) standard for course payments. This allows students to pay for courses by scanning a QR code with the Bakong mobile app.

## Table of Contents

- [Configuration](#configuration)
- [Payment Flow](#payment-flow)
- [API Endpoints](#api-endpoints)
  - [Generate QR Code](#1-generate-qr-code)
  - [Check Payment Status](#2-check-payment-status)
  - [Webhook (Payment Notification)](#3-webhook-payment-notification)
  - [Simulate Payment (Test Mode)](#4-simulate-payment-test-mode)
- [Mobile App Integration](#mobile-app-integration)
- [Error Handling](#error-handling)
- [Testing](#testing)

---

## Configuration

### Environment Variables

Add these variables to your `.env` file:

```env
# Bakong Payment (KHQR)
BAKONG_BASE_URL=https://api-bakong.nbc.gov.kh
BAKONG_MERCHANT_ID=your_merchant_id
BAKONG_MERCHANT_NAME=EduPlex
BAKONG_API_TOKEN=your_api_token
BAKONG_ACCOUNT_ID=your_bakong_account_id
BAKONG_ACQUIRING_BANK=your_bank_name
BAKONG_CURRENCY=USD
BAKONG_WEBHOOK_URL=https://yourdomain.com/api/bakong/webhook
BAKONG_WEBHOOK_SECRET=your_webhook_secret
BAKONG_TIMEOUT=30
BAKONG_VERIFY_SSL=true
BAKONG_TEST_MODE=true
```

### Configuration Options

| Variable | Description | Default |
|----------|-------------|---------|
| `BAKONG_BASE_URL` | Bakong API base URL | `https://api-bakong.nbc.gov.kh` |
| `BAKONG_MERCHANT_ID` | Your merchant ID from Bakong | Required |
| `BAKONG_MERCHANT_NAME` | Display name for merchant | `EduPlex` |
| `BAKONG_API_TOKEN` | API authentication token | Required |
| `BAKONG_ACCOUNT_ID` | Your Bakong account ID | Required |
| `BAKONG_ACQUIRING_BANK` | Your acquiring bank name | Required |
| `BAKONG_CURRENCY` | Payment currency (`USD` or `KHR`) | `USD` |
| `BAKONG_WEBHOOK_URL` | URL for payment notifications | Auto-generated |
| `BAKONG_WEBHOOK_SECRET` | Secret for webhook signature verification | Required |
| `BAKONG_TEST_MODE` | Enable test mode (no real payments) | `true` |

---

## Payment Flow

```
┌──────────────────────────────────────────────────────────────────────────┐
│                         COMPLETE PAYMENT FLOW                            │
├──────────────────────────────────────────────────────────────────────────┤
│                                                                          │
│  ┌─────────────┐     ┌─────────────┐     ┌─────────────┐                │
│  │   STEP 1    │     │   STEP 2    │     │   STEP 3    │                │
│  │  Scan QR    │────▶│  Generate   │────▶│  Scan with  │                │
│  │  (Enroll)   │     │  Bakong QR  │     │  Bakong App │                │
│  └─────────────┘     └─────────────┘     └─────────────┘                │
│        │                   │                   │                         │
│        ▼                   ▼                   ▼                         │
│  ┌─────────────┐     ┌─────────────┐     ┌─────────────┐                │
│  │ Enrollment  │     │  Payment    │     │   Bakong    │                │
│  │  Created    │     │   Record    │     │  Processes  │                │
│  │  (pending)  │     │  (pending)  │     │   Payment   │                │
│  └─────────────┘     └─────────────┘     └─────────────┘                │
│                                                │                         │
│                                                ▼                         │
│                            ┌──────────────────────────────┐             │
│                            │         STEP 4               │             │
│                            │  Payment Confirmed           │             │
│                            │  (Webhook or Polling)        │             │
│                            └──────────────────────────────┘             │
│                                                │                         │
│                                                ▼                         │
│                            ┌──────────────────────────────┐             │
│                            │         STEP 5               │             │
│                            │  ✓ payment_status = 'paid'   │             │
│                            │  ✓ Student can access course │             │
│                            │  ✓ Notifications sent        │             │
│                            └──────────────────────────────┘             │
│                                                                          │
└──────────────────────────────────────────────────────────────────────────┘
```

### Step-by-Step

1. **Student enrolls in course** (via QR code or direct enrollment)
2. **Student requests Bakong QR** for payment
3. **Student scans KHQR** with Bakong mobile app
4. **Bakong confirms payment** via webhook or polling
5. **System automatically unlocks course** for student

---

## API Endpoints

**Base URL:** `/api`

**Authentication:** All endpoints (except webhook) require Bearer token authentication.

```
Authorization: Bearer {token}
```

---

### 1. Generate QR Code

Generate a KHQR payment QR code for a course.

**Endpoint:** `POST /api/bakong/generate-qr`

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
  "course_id": 4
}
```

**Success Response (201):**
```json
{
  "success": true,
  "message": "QR code generated successfully",
  "data": {
    "payment_id": 15,
    "qr_string": "00020101021229...",
    "transaction_id": "EDU20260204QLTFQOY1",
    "amount": 129.99,
    "currency": "USD",
    "expires_at": "2026-02-04T09:41:23+00:00",
    "course": {
      "id": 4,
      "course_name": "Flutter & Dart Complete Course"
    }
  }
}
```

**Error Responses:**

| Status | Message | Description |
|--------|---------|-------------|
| 404 | Student profile not found | User is not a student |
| 422 | Course is not available | Course is not published |
| 422 | This course is free, no payment required | Course price is $0 |
| 422 | Please enroll in the course first | Student not enrolled |
| 422 | Course already paid | Already paid for this course |

**Example (cURL):**
```bash
curl -X POST https://yourdomain.com/api/bakong/generate-qr \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{"course_id": 4}'
```

---

### 2. Check Payment Status

Check the status of a Bakong payment.

**Endpoint:** `GET /api/bakong/check-status/{payment_id}`

**Headers:**
```
Authorization: Bearer {token}
```

**Success Response (200):**

**Pending:**
```json
{
  "success": true,
  "data": {
    "payment_id": 15,
    "status": "PENDING",
    "payment_status": "pending",
    "message": "Waiting for payment. Please scan the QR code with Bakong app.",
    "can_access_course": false,
    "expires_at": "2026-02-04T09:41:23+00:00"
  }
}
```

**Success:**
```json
{
  "success": true,
  "data": {
    "payment_id": 15,
    "status": "SUCCESS",
    "payment_status": "completed",
    "message": "Payment successful! You can now access the course.",
    "can_access_course": true
  }
}
```

**Expired:**
```json
{
  "success": true,
  "data": {
    "payment_id": 15,
    "status": "EXPIRED",
    "payment_status": "failed",
    "message": "QR code has expired. Please generate a new one.",
    "can_access_course": false
  }
}
```

**Status Values:**

| Status | Description |
|--------|-------------|
| `PENDING` | Waiting for payment |
| `SUCCESS` | Payment completed |
| `FAILED` | Payment failed |
| `EXPIRED` | QR code expired (15 minutes) |

**Example (cURL):**
```bash
curl -X GET https://yourdomain.com/api/bakong/check-status/15 \
  -H "Authorization: Bearer {token}"
```

---

### 3. Webhook (Payment Notification)

Bakong sends payment notifications to this endpoint.

**Endpoint:** `POST /api/bakong/webhook`

**Note:** This endpoint is public (no authentication required). Verification is done via signature.

**Headers from Bakong:**
```
X-Bakong-Signature: {hmac_sha256_signature}
Content-Type: application/json
```

**Request Body (from Bakong):**
```json
{
  "transaction_id": "EDU20260204QLTFQOY1",
  "md5": "abc123...",
  "status": "SUCCESS",
  "amount": 129.99,
  "currency": "USD",
  "timestamp": "2026-02-04T09:45:00+00:00"
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Webhook processed"
}
```

**What happens on successful webhook:**
1. Payment status updated to `completed`
2. Enrollment `payment_status` updated to `paid`
3. Student receives notification
4. Admin receives notification
5. Activity logged

---

### 4. Simulate Payment (Test Mode)

Simulate a successful payment for testing purposes.

**Note:** Only available when `BAKONG_TEST_MODE=true`

**Endpoint:** `POST /api/bakong/simulate/{payment_id}`

**Headers:**
```
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Payment simulated successfully",
  "can_access_course": true
}
```

**Error Response (403):**
```json
{
  "success": false,
  "message": "Simulation only available in test mode"
}
```

**Example (cURL):**
```bash
curl -X POST https://yourdomain.com/api/bakong/simulate/15 \
  -H "Authorization: Bearer {token}"
```

---

## Mobile App Integration

### Flutter/Dart Example

```dart
import 'package:http/http.dart' as http;
import 'dart:convert';

class BakongPaymentService {
  final String baseUrl;
  final String token;

  BakongPaymentService({required this.baseUrl, required this.token});

  // Generate Bakong QR
  Future<Map<String, dynamic>> generateQR(int courseId) async {
    final response = await http.post(
      Uri.parse('$baseUrl/api/bakong/generate-qr'),
      headers: {
        'Authorization': 'Bearer $token',
        'Content-Type': 'application/json',
      },
      body: jsonEncode({'course_id': courseId}),
    );

    return jsonDecode(response.body);
  }

  // Check payment status
  Future<Map<String, dynamic>> checkStatus(int paymentId) async {
    final response = await http.get(
      Uri.parse('$baseUrl/api/bakong/check-status/$paymentId'),
      headers: {'Authorization': 'Bearer $token'},
    );

    return jsonDecode(response.body);
  }

  // Poll for payment completion
  Future<bool> waitForPayment(int paymentId, {int maxAttempts = 60}) async {
    for (int i = 0; i < maxAttempts; i++) {
      final result = await checkStatus(paymentId);

      if (result['data']['status'] == 'SUCCESS') {
        return true;
      }

      if (result['data']['status'] == 'EXPIRED' ||
          result['data']['status'] == 'FAILED') {
        return false;
      }

      await Future.delayed(Duration(seconds: 5));
    }
    return false;
  }
}
```

### Display QR Code

Use any QR code library to display the `qr_string`:

```dart
import 'package:qr_flutter/qr_flutter.dart';

QrImageView(
  data: paymentData['qr_string'],
  version: QrVersions.auto,
  size: 250.0,
)
```

---

## Error Handling

### HTTP Status Codes

| Code | Description |
|------|-------------|
| 200 | Success |
| 201 | Created (QR generated) |
| 401 | Unauthorized (invalid token or webhook signature) |
| 402 | Payment Required (trying to access unpaid course) |
| 403 | Forbidden (test mode only endpoint) |
| 404 | Not Found (payment/course not found) |
| 422 | Validation Error |
| 500 | Server Error |

### Error Response Format

```json
{
  "success": false,
  "message": "Error description",
  "error": "Detailed error (optional)",
  "errors": ["Array of errors (optional)"]
}
```

---

## Testing

### Test Mode

When `BAKONG_TEST_MODE=true`:
- QR codes are simulated (not real Bakong QRs)
- Use `/api/bakong/simulate/{payment}` to simulate successful payment
- No actual money is transferred

### Test Flow

```bash
# 1. Login as student
TOKEN=$(curl -s -X POST https://yourdomain.com/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email": "student@test.com", "password": "password"}' \
  | jq -r '.data.token')

# 2. Enroll in a paid course
curl -X POST https://yourdomain.com/api/enrollments/enroll-by-code \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"code": "MO8ZCSOT"}'

# 3. Generate Bakong QR
PAYMENT_RESPONSE=$(curl -s -X POST https://yourdomain.com/api/bakong/generate-qr \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"course_id": 4}')

PAYMENT_ID=$(echo $PAYMENT_RESPONSE | jq -r '.data.payment_id')
echo "Payment ID: $PAYMENT_ID"

# 4. Check status (should be PENDING)
curl -X GET https://yourdomain.com/api/bakong/check-status/$PAYMENT_ID \
  -H "Authorization: Bearer $TOKEN"

# 5. Simulate payment success (test mode only)
curl -X POST https://yourdomain.com/api/bakong/simulate/$PAYMENT_ID \
  -H "Authorization: Bearer $TOKEN"

# 6. Check status again (should be SUCCESS)
curl -X GET https://yourdomain.com/api/bakong/check-status/$PAYMENT_ID \
  -H "Authorization: Bearer $TOKEN"

# 7. Try accessing a lesson (should work now)
curl -X GET https://yourdomain.com/api/lessons/1 \
  -H "Authorization: Bearer $TOKEN"
```

### Production Checklist

Before going to production:

- [ ] Set `BAKONG_TEST_MODE=false`
- [ ] Configure real Bakong credentials
- [ ] Set up webhook URL with Bakong
- [ ] Configure webhook secret
- [ ] Test with real Bakong account
- [ ] Enable SSL verification (`BAKONG_VERIFY_SSL=true`)

---

## Support

For issues with:
- **EduPlex Integration:** Contact EduPlex support
- **Bakong API:** Contact National Bank of Cambodia

---

*Last updated: February 2026*
