FROM golang:1.23-alpine AS builder

WORKDIR /build

COPY go/tools/go.mod ./
RUN go mod download -x 2>/dev/null || true

COPY go/tools/ ./

RUN CGO_ENABLED=0 GOOS=linux go build -mod=mod -ldflags="-s -w" -o healthcheck ./cmd/healthcheck
RUN CGO_ENABLED=0 GOOS=linux go build -mod=mod -ldflags="-s -w" -o backup       ./cmd/backup

# ---- Final image ----
FROM alpine:3.21

RUN apk add --no-cache ca-certificates tzdata postgresql-client

COPY --from=builder /build/healthcheck /usr/local/bin/healthcheck
COPY --from=builder /build/backup       /usr/local/bin/backup

# Default: run healthcheck; override CMD for backup
CMD ["healthcheck"]
