-- RMU (Read Model Updater) checkpoint table
-- Stores the last processed position (SequenceNumber) for each DynamoDB Stream Shard
CREATE TABLE IF NOT EXISTS rmu_checkpoint (
    shard_id VARCHAR(255) PRIMARY KEY,
    sequence_number VARCHAR(255) NOT NULL,
    stream_arn VARCHAR(512) NOT NULL,
    last_processed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Index for querying by stream ARN
CREATE INDEX idx_stream_arn ON rmu_checkpoint(stream_arn);

-- Index for monitoring processing lag
CREATE INDEX idx_last_processed_at ON rmu_checkpoint(last_processed_at);
