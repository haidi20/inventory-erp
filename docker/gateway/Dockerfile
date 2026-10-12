FROM golang:1.23-alpine AS builder

WORKDIR /build

# Download dependencies first (better layer caching)
COPY go/gateway/go.mod ./
RUN go mod download -x 2>/dev/null || true

COPY go/gateway/ ./
RUN CGO_ENABLED=0 GOOS=linux go build -mod=mod -ldflags="-s -w" -o gateway .

# ---- Final image ----
FROM alpine:3.21

RUN apk add --no-cache ca-certificates tzdata

WORKDIR /app
COPY --from=builder /build/gateway .

EXPOSE 9000
CMD ["./gateway"]
