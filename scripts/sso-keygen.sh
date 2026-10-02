#!/usr/bin/env bash
#
# Generate keypair RSA-2048 untuk SSO OpenSID (RS256).
# Private key HANYA di OpenKab; public key didistribusikan ke instalasi OpenSID.
#
# Usage: scripts/sso-keygen.sh [output_dir]
set -euo pipefail

OUT_DIR="${1:-storage/sso-keys}"

if [[ -f "${OUT_DIR}/private.pem" ]]; then
    echo "ERROR: ${OUT_DIR}/private.pem sudah ada. Hapus dulu bila ingin generate ulang." >&2
    exit 1
fi

mkdir -p "${OUT_DIR}"

openssl genpkey -algorithm RSA -pkeyopt rsa_keygen_bits:2048 -out "${OUT_DIR}/private.pem"
openssl pkey -in "${OUT_DIR}/private.pem" -pubout -out "${OUT_DIR}/public.pem"

chmod 600 "${OUT_DIR}/private.pem"
chmod 644 "${OUT_DIR}/public.pem"

echo "Keypair SSO dibuat di ${OUT_DIR}/"
echo "  private.pem -> isi SSO_SIGNING_PRIVATE_KEY_FILE (hanya OpenKab)"
echo "  public.pem  -> distribusikan ke OpenSID / isi SSO_SIGNING_PUBLIC_KEY_FILE"
