# AWS Production Deployment Architecture: MediTrack

This document outlines the recommended AWS production cloud architecture for deploying the **MediTrack** Medical Case Management application with high availability, regulatory data protection, and automated scaling.

---

```
                       [ Internet Users / Hospital Clients ]
                                        │
                                        ▼ HTTPS (Port 443)
                           [ AWS Route 53 (DNS) ]
                                        │
                                        ▼
                  [ AWS Application Load Balancer (ALB) ]
                     • AWS Certificate Manager (ACM SSL)
                     • Automated HTTPS Redirection
                                        │
                    ┌───────────────────┴───────────────────┐
                    ▼ Private Subnet                        ▼ Private Subnet
        [ EC2 Instance 1 (AZ-A) ]               [ EC2 Instance 2 (AZ-B) ]
        • Ubuntu LTS + Nginx + PHP 8.3-FPM      • Ubuntu LTS + Nginx + PHP 8.3-FPM
        • Supervisor (Queues & Scheduler)       • Supervisor (Queues & Scheduler)
        • AWS Systems Manager Agent             • AWS Systems Manager Agent
                    │                                       │
                    └───────────────────┬───────────────────┘
                                        │
                 ┌──────────────────────┴──────────────────────┐
                 ▼ Private DB Subnet                           ▼
       [ AWS RDS for MySQL ]                         [ AWS S3 Bucket ]
       • Multi-AZ Deployment                         • Case attachments & CAD files
       • Automated daily snapshots                   • AES-256 Server-Side Encryption
       • KMS At-Rest Encryption                      • Versioning & Strict IAM Bucket Policy
```

---

## 1. Core AWS Services Breakdown

### 1.1 Compute: AWS EC2 & Auto Scaling
- **Instances:** AWS EC2 instances running Ubuntu 24.04 LTS provisioned within private subnets across multiple Availability Zones (Multi-AZ).
- **Web Stack:** Nginx reverse proxy communicating via UNIX sockets to PHP 8.3-FPM with OPcache enabled.
- **Background Tasks:** Laravel queues, email notifications, and scheduled cron jobs managed by `supervisord`.

### 1.2 Database: Amazon RDS for MySQL
- **Engine:** MySQL 8.0+ running on an RDS Multi-AZ instance with automatic failover.
- **Storage & Security:** Amazon EBS gp3 encrypted with AWS Key Management Service (KMS). Automated point-in-time recovery and automated daily snapshots retained for compliance.
- **Network Isolation:** Placed in isolated database subnets without public internet access.

### 1.3 File Storage: Amazon S3
- **Object Storage:** Stores medical device CAD specifications, surgical scans, and export documents.
- **Security:** Bucket configured with `Block Public Access`, SSE-S3 / SSE-KMS encryption at rest, lifecycle archiving to S3 Glacier, and pre-signed temporary URLs for download access.

---

## 2. Security Groups & Network Architecture

| Security Group | Inbound Rules | Outbound Rules | Purpose |
| :--- | :--- | :--- | :--- |
| **`sg-alb`** | • `443/TCP` from `0.0.0.0/0`<br>• `80/TCP` from `0.0.0.0/0` | All traffic to `sg-app` on port 80 | Public entry point for HTTPS traffic. |
| **`sg-app`** | • `80/TCP` from `sg-alb` only | • `3306/TCP` to `sg-rds`<br>• `443/TCP` out (AWS APIs, Nager.Date) | EC2 application servers; completely closed to direct public internet access. |
| **`sg-rds`** | • `3306/TCP` from `sg-app` only | None | MySQL database; strictly accessible only by application servers. |

---

## 3. Secrets Management & Environment Configuration
- **AWS Systems Manager (SSM) Parameter Store / AWS Secrets Manager:**
  - Sensitive credentials (`APP_KEY`, `DB_PASSWORD`, `SANCTUM_STATEFUL_DOMAINS`) are stored encrypted with KMS.
  - Deployments pull secrets dynamically via AWS CLI / instance IAM role at startup into runtime memory, ensuring `.env` files with plaintext passwords are never saved into Git or AMI images.

---

## 4. HTTPS, SSL/TLS & Edge Performance
- **SSL Certificate:** Provisioned at zero cost via **AWS Certificate Manager (ACM)** and attached to the Application Load Balancer.
- **Enforcement:** ALB listener rule on HTTP:80 issues a permanent 301 redirect to HTTPS:443 with TLS 1.3 enforced.
- **HSTS Headers:** Nginx and Laravel send `Strict-Transport-Security: max-age=31536000; includeSubDomains` headers to prevent man-in-the-middle attacks.
