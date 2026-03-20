import React from 'react'
import Card from '@/components/ui/Card'
import Button from '@/components/ui/Button'

interface TranscriptCardProps {
  transcript: string
  transcriptError: string | null
  isTranscribing: boolean
  hasRecording: boolean
  onTranscribe: () => void
}

export default function TranscriptCard({
  transcript,
  transcriptError,
  isTranscribing,
  hasRecording,
  onTranscribe
}: TranscriptCardProps) {
  return (
    <Card className="p-6">
      <div className="flex justify-between items-center mb-4">
        <h3 className="text-lg font-bold text-navy-900">Transcript</h3>
        {!transcript && !transcriptError && hasRecording && (
          <Button 
            variant="secondary" 
            size="sm"
            onClick={onTranscribe}
            disabled={isTranscribing}
          >
            Transcribe
          </Button>
        )}
      </div>

      {isTranscribing ? (
        <div className="bg-navy-50 rounded-lg p-4 text-navy-600 text-sm flex items-center gap-3">
          <div className="w-5 h-5 border-2 border-navy-400 border-t-transparent rounded-full animate-spin" />
          <span>Transcribing audio with AssemblyAI...</span>
        </div>
      ) : transcript ? (
        <div className="bg-navy-50 rounded-lg p-4 text-navy-700 text-sm leading-relaxed whitespace-pre-wrap max-h-96 overflow-y-auto">
          {transcript}
        </div>
      ) : transcriptError ? (
        <div className="bg-amber-50 rounded-lg p-4 text-amber-700 text-sm">
          <div className="flex items-start gap-2">
            <svg className="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <div>
              <p className="font-medium">Transcription Failed</p>
              <p className="text-amber-600 mt-1">{transcriptError}</p>
            </div>
          </div>
        </div>
      ) : (
        <div className="bg-navy-50 rounded-lg p-4 text-navy-400 text-sm text-center">
          No transcript available. {hasRecording && 'Click Transcribe to generate one.'}
        </div>
      )}
    </Card>
  )
}
