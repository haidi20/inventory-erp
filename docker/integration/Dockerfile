FROM golang:1.23-alpine AS builder

WORKDIR /build

# Download dependencies first (better layer caching)
COPY go/integration/go.mod ./
RUN go mod download -x 2>/dev/null || true

COPY go/integration/ ./
RUN CGO_ENABLED=0 GOOS=linux go build -mod=mod -ldflags="-s -w" -o integration .

# ---- Final image ----
FROM alpine:3.21

RUN apk add --no-cache ca-certificates tzdata

WORKDIR /app
COPY --from=builder /build/integration .

EXPOSE 9001
CMD ["./integration"]
