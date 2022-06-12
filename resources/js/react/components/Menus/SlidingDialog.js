import { Box, Button, Modal, Paper, Slide } from '@mui/material'
import React, { Component } from 'react'
import { doesExist, updateState } from '../helpers';

export class SlidingDialog extends Component {
    constructor(props) {
        super(props);

        this.handleModalClose = this.handleModalClose.bind(this);
        this.handleModalOpen = this.handleModalOpen.bind(this);

        this.state = {
            emailVerificationSlide: false,
            modalOpen: false,
            emailVerificationSlideTimeout: 300
        };
    }

    modalClose(timeout = null) {
        updateState(this, { emailVerificationSlide: false });

        setTimeout(() => {
            updateState(this, { modalOpen: false });
        }, timeout ? timeout : this.state.emailVerificationSlideTimeout);
    }

    modalOpen() {
        updateState(this, { modalOpen: true, emailVerificationSlide: true });
    }

    handleModalClose(timeout = null) {
        if (doesExist(this.props.open)) {
            return;
        }

        this.modalClose(timeout);
    }

    handleModalOpen(e = null) {
        if (doesExist(this.props.open)) {
            return;
        }

        this.modalOpen();
    }

    handleModal(bool, timeout = null) {
        if (bool) {
            this.modalOpen();
        } else {
            this.modalClose(timeout ? timeout : this.state.emailVerificationSlideTimeout);
        }
    }

    componentDidUpdate(prevProps) {
        if (
            doesExist(this.props.open) &&
            this.state.modalOpen !== this.props.open &&
            this.state.emailVerificationSlide !== this.props.open
        ) {
            this.handleModal(this.props.open, this.props.timeout ? this.props.timeout : null);
        }
    }

    render() {
        if (this.props.slideTrigger && this.props.slideTriggerProps && !('variant' in this.props.slideTriggerProps)) {
            this.props.slideTriggerProps.variant = 'contained';
        }

        return (
            <>
                {this.props.slideTrigger}
                {!this.props.slideTrigger &&
                    <Button {...this.props.slideTriggerProps} onClick={this.handleModalOpen}>
                        {this.props.slideTriggerInner}
                    </Button>
                }
                <Modal
                    component='div'
                    open={this.state.modalOpen}
                    onClose={() => {
                        if (this.props.slideTrigger) {
                            this.props.onClose();
                        } else {
                            this.handleModalClose();
                        }
                    }}
                >
                    <Box style={{ top: '50%', left: '50%', position: 'absolute', transform: 'translate(-50%, -50%)', }}>
                        <div>
                            <Slide direction="up" in={this.state.emailVerificationSlide} mountOnEnter unmountOnExit timeout={this.state.emailVerificationSlideTimeout}>
                                <Paper elevation={1} sx={{ m: 2, p: 2 }}>
                                    {this.props.children}
                                </Paper>
                            </Slide>
                        </div>
                    </Box>
                </Modal>
            </>
        );
    }
}

export default SlidingDialog
